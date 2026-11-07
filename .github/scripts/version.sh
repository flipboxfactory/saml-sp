#!/usr/bin/env bash
# Release versioning, driven by composer.json "version" and CHANGELOG.md.
#
#   version.sh check <base-sha>  PR gate: if src/ or composer.json changed, the version must be
#                                greater than the latest tag and have a CHANGELOG heading.
#   version.sh notes <version>   Print the CHANGELOG section for <version>.
#   version.sh release           On master: create the tag + GitHub release for a new version.
set -euo pipefail

fail() { echo "::error::$*" >&2; exit 1; }

version() { jq -r '.version' composer.json; }

latest_tag() { git tag --list '[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname | head -n1; }

# Section under a heading like "# 1.2.3 2026-01-01" or "## 1.2.3 - 2026-01-01", up to the next
# heading of the same or higher level.
notes() {
    awk -v v="$1" '
        /^#+ / { match($0, /^#+/); level = RLENGTH; split($0, f, " ")
                 if (found && level <= want) exit
                 if (!found && f[2] == v) { found = 1; want = level; next } }
        found { print }
    ' CHANGELOG.md
}

check() {
    local base=$1 v latest extra changed
    # Separate assignment so a failing git diff aborts (fails closed) instead of reading as "no changes"
    changed=$(git diff --name-only "$base" HEAD)
    if ! grep -qE '^(src/|composer\.json$)' <<<"$changed"; then
        echo "No src/ or composer.json changes; no version bump required."
        return
    fi

    v=$(version)
    extra=$(jq -r '.extra.version // empty' composer.json)
    [ -z "$extra" ] || [ "$extra" = "$v" ] || fail "composer.json extra.version ($extra) must match version ($v)."

    latest=$(latest_tag)
    if [ -n "$latest" ] && { [ "$v" = "$latest" ] || [ "$(printf '%s\n%s\n' "$latest" "$v" | sort -V | tail -n1)" != "$v" ]; }; then
        fail "Bump composer.json version: $v is not greater than the latest tag $latest."
    fi

    [ -n "$(notes "$v")" ] || fail "Add a CHANGELOG.md entry for $v."
    echo "OK: merging will release $v (latest tag: ${latest:-none})."
}

release() {
    local v
    v=$(version)
    if git rev-parse -q --verify "refs/tags/$v" >/dev/null; then
        echo "Tag $v already exists; nothing to release."
        return
    fi

    notes "$v" > "${RUNNER_TEMP:-/tmp}/release-notes.md"
    [ -s "${RUNNER_TEMP:-/tmp}/release-notes.md" ] || fail "No CHANGELOG.md entry for $v."

    gh release create "$v" --target "${GITHUB_SHA:?}" --title "$v" --notes-file "${RUNNER_TEMP:-/tmp}/release-notes.md"
}

case "${1:-}" in
    check) check "${2:?base sha required}" ;;
    notes) notes "${2:?version required}" ;;
    release) release ;;
    *) fail "usage: $0 check <base-sha> | notes <version> | release" ;;
esac
