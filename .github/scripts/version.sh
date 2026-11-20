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
# A security advisory merge has no PR here: GitHub squashes it as "Merge commit from fork", and its
# body's "* <subject>" lines (the private fork's commits, which must be conventional) decide the release.
#
#   version.sh bump <title> [body]  Print major|minor|patch|none; fails if <title> isn't conventional.
#   version.sh check <base-sha>     PR gate (env PR_TITLE, PR_BODY): conventional title, composer.json version
#                                   untouched, and a releasing PR has CHANGELOG "Unreleased" notes.
#   version.sh plan                 On the release branch: print the next version (also to $GITHUB_OUTPUT), or nothing if
#                                   the PRs merged since the latest tag don't release. Also creates a missing
#                                   GitHub release for the latest tag.
#   version.sh release <version>    On the release branch: date the Unreleased section, set composer.json version, push a
#                                   release commit + tag (env APP_TOKEN, APP_SLUG), create the GitHub release.
#   version.sh notes <version>      Print the CHANGELOG section for <version> (or "Unreleased").
#
# The release branch is $RELEASE_BRANCH, else the pushed branch ($GITHUB_REF_NAME), else master. Versions
# continue from the newest tag reachable from HEAD, so a maintenance branch (craft/5.0) releases 5.x while
# master releases 6.x.
#
# Prereleases: while .github/prerelease exists (containing alpha, beta or rc), releases are prereleases.
#   From a stable tag, the bump starts a new prerelease series: 5.2.9 + feat!: -> 6.0.0-beta.1.
#   From a prerelease tag, any releasing PR continues the series, whatever its bump: 6.0.0-beta.1 -> 6.0.0-beta.2.
#   Changing the label restarts the number: 6.0.0-beta.3 -> 6.0.0-rc.1.
#   Deleting .github/prerelease in a releasing PR publishes the final version: 6.0.0-rc.2 -> 6.0.0.
# Prereleases are GitHub prereleases; a release lower than the highest stable tag (a 5.x fix after 6.0.0) isn't
# marked as the latest release.
set -euo pipefail

TYPES='feat|fix|perf|docs|test|ci|chore|build|refactor|style|revert'
RELEASE_SUBJECT='chore(release): '
# Subject GitHub gives the squash commit when a security advisory's private-fork PRs are merged
ADVISORY_SUBJECT='Merge commit from fork'
VERSION_HEADING='^#+ v?[0-9]+\.[0-9]+\.[0-9]+'
VERSION_RE='^([0-9]+)\.([0-9]+)\.([0-9]+)(-(alpha|beta|rc)\.([0-9]+))?$'
PRERELEASE_FILE=.github/prerelease
NOTES_FILE="${RUNNER_TEMP:-/tmp}/release-notes.md"
TITLES_FILE="${RUNNER_TEMP:-/tmp}/release-titles.md"

BRANCH=${RELEASE_BRANCH:-${GITHUB_REF_NAME:-master}}

fail() { echo "::error::$*" >&2; exit 1; }

# Release tags, newest first. versionsort.suffix sorts 6.0.0-beta.1 before 6.0.0, and ^ (from the right) after
# 5.2.9; only tags matching VERSION_RE count.
tags() { git -c versionsort.suffix=- tag "$@" --list '[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname | grep -E "$VERSION_RE" || true; }

# Newest release tag on this branch's history (other branches' tags, e.g. 6.x on master from craft/5.0, don't count)
latest_tag() { tags --merged HEAD | head -n1; }

# Prerelease label from .github/prerelease in the working tree, or empty
prerelease_label() {
    local label
    [ -f "$PRERELEASE_FILE" ] || return 0
    label=$(tr -d '[:space:]' <"$PRERELEASE_FILE")
    [[ $label =~ ^(alpha|beta|rc)$ ]] || fail "$PRERELEASE_FILE must contain alpha, beta or rc, not \"$label\"."
    echo "$label"
}

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

# next <latest> <bump> [<prerelease label>]: the version after <latest> for a major|minor|patch bump
next() {
    local latest=$1 b=$2 label=${3:-} major minor patch base pre num
    [[ $latest =~ $VERSION_RE ]] || fail "Invalid version: $latest"
    major=${BASH_REMATCH[1]} minor=${BASH_REMATCH[2]} patch=${BASH_REMATCH[3]} pre=${BASH_REMATCH[5]} num=${BASH_REMATCH[6]}
    if [ -n "$pre" ]; then
        # A prerelease series ends in its own version: continue it, or publish it
        base="$major.$minor.$patch"
        if [ -z "$label" ]; then
            echo "$base"
        elif [ "$label" = "$pre" ]; then
            echo "$base-$label.$((num + 1))"
        elif [ "$(rank_label "$label")" -gt "$(rank_label "$pre")" ]; then
            echo "$base-$label.1"
        else
            fail "Can't go from $latest back to a $label prerelease: $PRERELEASE_FILE must say $pre or later."
        fi
        return
    fi
    case $b in
        major) base="$((major + 1)).0.0" ;;
        minor) base="$major.$((minor + 1)).0" ;;
        patch) base="$major.$minor.$((patch + 1))" ;;
    esac
    echo "$base${label:+-$label.1}"
}

rank_label() { case $1 in alpha) echo 1 ;; beta) echo 2 ;; rc) echo 3 ;; esac; }

# gh release create, as a prerelease for X.Y.Z-label.N, and not "latest" below the highest stable tag
gh_release() {
    local v=$1 top flags=()
    if [[ $v == *-* ]]; then
        flags+=(--prerelease)
    else
        top=$(tags | grep -vF -e - | head -n1)
        [ "$top" = "$v" ] || flags+=(--latest=false)
    fi
    gh release create "$v" --verify-tag --title "$v" --notes-file "$NOTES_FILE" ${flags[@]+"${flags[@]}"}
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
    local base=$1 b changed key latest u first top v
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

    v=$(next "$latest" "$b" "$(prerelease_label)")
    if [[ $v == *-*.1 && $latest != *-* && $b != major ]]; then
        echo "::warning::This starts a $v prerelease series from $latest. Prerelease series are meant for the next major (a <type>!: title); remove $PRERELEASE_FILE if this should be a normal release."
    fi
    ! git rev-parse -q --verify "refs/tags/$v" >/dev/null ||
        fail "\"$PR_TITLE\" would release $v, which is already tagged: update this branch from its base, or, on a maintenance branch, a $b release isn't possible."

    u=$(unreleased_heading)
    first=$(version_heading)
    [ -n "$u" ] && [ -n "$(notes Unreleased | tr -d '[:space:]')" ] ||
        fail "\"$PR_TITLE\" releases a $b version: add release notes to CHANGELOG.md under an \"Unreleased\" heading at the top, at the same level as the version headings (append \" [CRITICAL]\" for a critical release)."
    [ "$(unreleased_headings | wc -l | tr -d ' ')" -eq 1 ] || fail "CHANGELOG.md has more than one Unreleased heading."
    [ -z "$first" ] || [ "${u#* }" = "${first#* }" ] ||
        fail "The CHANGELOG.md Unreleased heading must use \"${first#* }\", like the version headings."
    echo "OK: merging releases $v ($b, from $latest)."
}

plan() {
    local sha=${GITHUB_SHA:?} later latest b=none c info title body num cb v titles t
    git_auth "${GH_TOKEN:?}" fetch -q --tags origin "$BRANCH"

    # Another PR merged after this one: its run releases both, once its checks pass.
    later=$(git log --format=%s "$sha..origin/$BRANCH" | grep -vF "$RELEASE_SUBJECT" || true)
    if [ -n "$later" ]; then
        echo "$BRANCH has moved past $sha; the run for the newest commit releases instead."
        return
    fi
    git checkout -q "origin/$BRANCH"

    latest=$(latest_tag)
    [ -n "$latest" ] || fail "No release tag found."
    if ! gh release view "$latest" >/dev/null 2>&1; then
        echo "Tag $latest has no GitHub release; creating it."
        notes "$latest" >"$NOTES_FILE"
        gh_release "$latest"
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
        titles=$title
        if [ -z "$info" ] && [ "$title" = "$ADVISORY_SUBJECT" ]; then
            # A security advisory's private-fork PRs land as one "Merge commit from fork" squash, with no PR in
            # this repo; its body lists the squashed commits as "* <subject>". Release from those subjects.
            titles=$(sed -n 's/^\* //p' <<<"$body")
            [ -n "$titles" ] || titles=$title
        fi
        while IFS= read -r t; do
            case $t in "$RELEASE_SUBJECT"*) continue ;; esac
            if ! cb=$(bump "$t" "$body" 2>/dev/null); then
                echo "::warning::${c:0:7} \"$t\" isn't a conventional commit title, so it doesn't count towards the release."
                continue
            fi
            echo "${c:0:7} $cb: $t"
            [ "$(rank "$cb")" -le "$(rank "$b")" ] || b=$cb
            [ "$cb" = none ] || echo "- ${t#*: }$num" >>"$TITLES_FILE"
        done <<<"$titles"
    done

    if [ "$b" = none ]; then
        echo "Nothing to release since $latest."
        return
    fi
    v=$(next "$latest" "$b" "$(prerelease_label)")
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
    [[ $v =~ $VERSION_RE ]] || fail "Invalid version: $v"
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

    if ! git_auth "$APP_TOKEN" push --atomic origin "HEAD:refs/heads/$BRANCH" "refs/tags/$v"; then
        git_auth "$GH_TOKEN" fetch -q origin "$BRANCH"
        [ "$(git rev-parse "origin/$BRANCH")" != "$parent" ] || fail "Couldn't push the $v release commit and tag to $BRANCH."
        echo "::warning::$BRANCH moved on while releasing $v; the run for the newest commit releases instead."
        return
    fi
    gh_release "$v"
}

case "${1:-}" in
    bump) bump "${2:?title required}" "${3:-}" ;;
    check) check "${2:?base sha required}" ;;
    plan) plan ;;
    release) release "${2:-}" ;;
    notes) notes "${2:?version required}" ;;
    *) fail "usage: $0 bump <title> [body] | check <base-sha> | plan | release <version> | notes <version>" ;;
esac
