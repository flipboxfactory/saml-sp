#!/usr/bin/env bash
# Release versioning, driven by conventional-commit PR titles. Nobody bumps the version by hand.
#
# The PR title decides the release (PRs are squash-merged, so the title is the commit subject):
#   feat: ...                                         minor
#   fix: ... / perf: ...                              patch
#   <type>!: ... or a "BREAKING CHANGE:" line in the PR body   major
#   docs|test|ci|chore|build|refactor|style|revert:   no release
# An optional scope is allowed: "fix(sso): ...". Release notes go under an "Unreleased" heading at
# the top of CHANGELOG.md (append " [CRITICAL]" for a critical release); CI replaces it with the version.
#
#   version.sh bump <title> [body]  Print major|minor|patch|none; fails if <title> isn't conventional.
#   version.sh check <base-sha>     PR gate (env PR_TITLE, PR_BODY): conventional title, composer.json version
#                                   untouched, and a releasing PR has CHANGELOG "Unreleased" notes.
#   version.sh plan                 On master: print the next version (also to $GITHUB_OUTPUT), or nothing if
#                                   the PRs merged since the latest tag don't release. Also creates a missing
#                                   GitHub release for the latest tag.
#   version.sh release <version>    On master: date the Unreleased section, set composer.json version, push a
#                                   release commit + tag (env APP_TOKEN, APP_SLUG), create the GitHub release.
#   version.sh notes <version>      Print the CHANGELOG section for <version> (or "Unreleased").
set -euo pipefail

TYPES='feat|fix|perf|docs|test|ci|chore|build|refactor|style|revert'
RELEASE_SUBJECT='chore(release): '
VERSION_HEADING='^#+ v?[0-9]+\.[0-9]+\.[0-9]+'
NOTES_FILE="${RUNNER_TEMP:-/tmp}/release-notes.md"
TITLES_FILE="${RUNNER_TEMP:-/tmp}/release-titles.md"

fail() { echo "::error::$*" >&2; exit 1; }

latest_tag() { git tag --list '[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname | head -n1; }

# git with a token for github.com (checkout runs with persist-credentials: false)
git_auth() {
    local token=$1; shift
    git -c "http.https://github.com/.extraheader=AUTHORIZATION: basic $(printf 'x-access-token:%s' "$token" | base64 | tr -d '\n')" "$@"
}

bump() {
    local title=$1 body=${2:-} re="^($TYPES)(\([^)]+\))?(!)?: [^ ]"
    [[ $title =~ $re ]] || fail "\"$title\" is not a conventional commit title, e.g. \"fix: ...\", \"feat(sso): ...\" or \"feat!: ...\" (types: ${TYPES//|/, })."
    if [ -n "${BASH_REMATCH[3]}" ] || grep -qE '^BREAKING[ -]CHANGE:' <<<"$body"; then
        echo major
        return
    fi
    case ${BASH_REMATCH[1]} in
        feat) echo minor ;;
        fix|perf) echo patch ;;
        *) echo none ;;
    esac
}

rank() { case $1 in major) echo 3 ;; minor) echo 2 ;; patch) echo 1 ;; *) echo 0 ;; esac; }

next() {
    local major minor patch
    IFS=. read -r major minor patch <<<"$1"
    case $2 in
        major) echo "$((major + 1)).0.0" ;;
        minor) echo "$major.$((minor + 1)).0" ;;
        patch) echo "$major.$minor.$((patch + 1))" ;;
    esac
}

# Section under a heading like "# 1.2.3 2026-01-01", "## 1.2.3 - 2026-01-01" or "## Unreleased", up to the
# next heading of the same or higher level.
notes() {
    awk -v v="$1" '
        /^#+ / { match($0, /^#+/); level = RLENGTH; split($0, f, " ")
                 if (found && level <= want) exit
                 if (!found && f[2] == v) { found = 1; want = level; next }
                 # Only an Unreleased section above the newest version counts
                 if (!found && v == "Unreleased" && f[2] ~ /^v?[0-9]+\.[0-9]+\.[0-9]+/) exit }
        found { print }
    ' CHANGELOG.md
}

# "<line> <hashes>" of the newest version heading / the Unreleased heading above it (old changelogs can
# have historical "Unreleased" headings further down, which are ignored); empty if absent
version_heading() { grep -nE "$VERSION_HEADING" CHANGELOG.md | head -n1 | sed -E 's/^([0-9]+):(#+).*/\1 \2/' || true; }
unreleased_headings() {
    local first
    first=$(version_heading)
    awk -v stop="${first%% *}" 'stop != "" && NR >= stop { exit } /^#+ Unreleased( |$)/ { match($0, /^#+/); print NR " " substr($0, 1, RLENGTH) }' CHANGELOG.md
}
unreleased_heading() { unreleased_headings | head -n1; }

check() {
    local base=$1 b changed key latest u first top
    b=$(bump "${PR_TITLE:?PR_TITLE is required}" "${PR_BODY:-}")

    for key in .version '.extra.version // empty'; do
        [ "$(git show "$base:composer.json" | jq -r "$key")" = "$(jq -r "$key" composer.json)" ] ||
            fail "Don't change composer.json ${key%% *} by hand: CI sets it when the PR is merged."
    done

    latest=$(latest_tag)
    [ -n "$latest" ] || fail "No release tag found."
    # Separate assignment so a failing git diff aborts (fails closed) instead of reading as "no changes"
    changed=$(git diff --name-only "$base" HEAD)

    top=$(grep -m1 -E "$VERSION_HEADING" CHANGELOG.md | awk '{ sub(/^v/, "", $2); print $2 }' || true)
    [ -z "$top" ] || git rev-parse -q --verify "refs/tags/$top" >/dev/null ||
        fail "CHANGELOG.md has a heading for $top, which isn't released. Put the notes under \"Unreleased\" instead: CI adds the version and date."

    if [ "$b" = none ]; then
        if grep -qE '^(src/|composer\.json$)' <<<"$changed"; then
            echo "::warning::This PR changes src/ or composer.json but \"$PR_TITLE\" doesn't release. Use fix:, feat: or <type>!: if it should."
        fi
        echo "OK: \"$PR_TITLE\" doesn't create a release."
        return
    fi

    u=$(unreleased_heading)
    first=$(version_heading)
    [ -n "$u" ] && [ -n "$(notes Unreleased | tr -d '[:space:]')" ] ||
        fail "\"$PR_TITLE\" releases a $b version: add release notes to CHANGELOG.md under an \"Unreleased\" heading at the top, at the same level as the version headings (append \" [CRITICAL]\" for a critical release)."
    [ "$(unreleased_headings | wc -l | tr -d ' ')" -eq 1 ] || fail "CHANGELOG.md has more than one Unreleased heading."
    [ -z "$first" ] || [ "${u#* }" = "${first#* }" ] ||
        fail "The CHANGELOG.md Unreleased heading must use \"${first#* }\", like the version headings."
    echo "OK: merging releases $(next "$latest" "$b") ($b, from $latest)."
}

plan() {
    local sha=${GITHUB_SHA:?} later latest b=none c info title body num cb v
    git_auth "${GH_TOKEN:?}" fetch -q --tags origin master

    # Another PR merged after this one: its run releases both, once its checks pass.
    later=$(git log --format=%s "$sha..origin/master" | grep -vF "$RELEASE_SUBJECT" || true)
    if [ -n "$later" ]; then
        echo "master has moved past $sha; the run for the newest commit releases instead."
        return
    fi
    git checkout -q origin/master

    latest=$(latest_tag)
    [ -n "$latest" ] || fail "No release tag found."
    if ! gh release view "$latest" >/dev/null 2>&1; then
        echo "Tag $latest has no GitHub release; creating it."
        notes "$latest" >"$NOTES_FILE"
        gh release create "$latest" --verify-tag --title "$latest" --notes-file "$NOTES_FILE"
    fi

    : >"$TITLES_FILE"
    for c in $(git rev-list --reverse "$latest..HEAD"); do
        info=$(gh api "repos/${GITHUB_REPOSITORY:?}/commits/$c/pulls" --jq '[.[] | select(.merged_at != null)][0] // empty')
        if [ -n "$info" ]; then
            title=$(jq -r .title <<<"$info")
            body=$(jq -r '.body // ""' <<<"$info")
            num=" (#$(jq -r .number <<<"$info"))"
        else
            title=$(git log -1 --format=%s "$c")
            body=$(git log -1 --format=%b "$c")
            num=""
        fi
        case $title in "$RELEASE_SUBJECT"*) continue ;; esac
        if ! cb=$(bump "$title" "$body" 2>/dev/null); then
            echo "::warning::${c:0:7} \"$title\" isn't a conventional commit title, so it doesn't count towards the release."
            continue
        fi
        echo "${c:0:7} $cb: $title"
        [ "$(rank "$cb")" -le "$(rank "$b")" ] || b=$cb
        [ "$cb" = none ] || echo "- ${title#*: }$num" >>"$TITLES_FILE"
    done

    if [ "$b" = none ]; then
        echo "Nothing to release since $latest."
        return
    fi
    v=$(next "$latest" "$b")
    echo "Next version: $v ($b, from $latest)"
    [ -z "${GITHUB_OUTPUT:-}" ] || echo "version=$v" >>"$GITHUB_OUTPUT"
}

# Rename the Unreleased heading to "<version><sep><date>", matching the newest version heading's style
# ("## 1.2.3 - 2026-01-01" or "# 1.2.3 2026-01-01"). Without one, add a section from the PR titles.
stamp_changelog() {
    local v=$1 date=$2 sep=" - " level="##" newest u first tmp
    newest=$(grep -m1 -E "$VERSION_HEADING" CHANGELOG.md || true)
    if [ -n "$newest" ]; then
        level=${newest%% *}
        [[ $newest =~ ^#+\ [^\ ]+\ -\  ]] || sep=" "
    fi
    u=$(unreleased_heading)
    tmp=$(mktemp)
    if [ -n "$u" ]; then
        awk -v n="${u%% *}" -v r="$v$sep$date" 'NR == n { sub(/Unreleased/, r) } { print }' CHANGELOG.md >"$tmp"
    else
        echo "::warning::CHANGELOG.md has no Unreleased section; using the PR titles."
        first=$(version_heading)
        # Insert before the newest version heading, or append if there is none
        awk -v n="${first%% *}" -v h="$level $v$sep$date" -v tf="$TITLES_FILE" '
            function section() { print h; print ""; while ((getline l < tf) > 0) print l; print "" }
            NR == n { section() } { print } END { if (n == "") { print ""; section() } }
        ' CHANGELOG.md >"$tmp"
    fi
    cat "$tmp" >CHANGELOG.md
    rm -f "$tmp"
}

release() {
    local v=${1:?version required} parent bot_id
    [[ $v =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || fail "Invalid version: $v"
    : "${APP_TOKEN:?APP_TOKEN is required}" "${APP_SLUG:?APP_SLUG is required}" "${GH_TOKEN:?}"
    ! git rev-parse -q --verify "refs/tags/$v" >/dev/null || fail "Tag $v already exists."

    stamp_changelog "$v" "$(date -u +%F)"
    # Only the exact "version" keys (top level and extra), not e.g. "schemaVersion"; keeps the file's formatting
    sed -E -i.bak 's/^([[:space:]]*"version"[[:space:]]*:[[:space:]]*")[^"]*"/\1'"$v"'"/' composer.json
    rm -f composer.json.bak
    [ "$(jq -r '[.version, (.extra.version // .version)] | unique | join(" ")' composer.json)" = "$v" ] ||
        fail "Couldn't set composer.json version to $v."
    notes "$v" >"$NOTES_FILE"
    [ -s "$NOTES_FILE" ] || fail "No CHANGELOG.md notes for $v."

    bot_id=$(gh api "users/${APP_SLUG}%5Bbot%5D" --jq .id)
    parent=$(git rev-parse HEAD)
    git -c user.name="${APP_SLUG}[bot]" -c user.email="${bot_id}+${APP_SLUG}[bot]@users.noreply.github.com" \
        commit -q -m "${RELEASE_SUBJECT}$v [skip ci]" -- CHANGELOG.md composer.json
    git tag "$v"

    if ! git_auth "$APP_TOKEN" push --atomic origin HEAD:refs/heads/master "refs/tags/$v"; then
        git_auth "$GH_TOKEN" fetch -q origin master
        [ "$(git rev-parse origin/master)" != "$parent" ] || fail "Couldn't push the $v release commit and tag to master."
        echo "::warning::master moved on while releasing $v; the run for the newest commit releases instead."
        return
    fi
    gh release create "$v" --verify-tag --title "$v" --notes-file "$NOTES_FILE"
}

case "${1:-}" in
    bump) bump "${2:?title required}" "${3:-}" ;;
    check) check "${2:?base sha required}" ;;
    plan) plan ;;
    release) release "${2:-}" ;;
    notes) notes "${2:?version required}" ;;
    *) fail "usage: $0 bump <title> [body] | check <base-sha> | plan | release <version> | notes <version>" ;;
esac
