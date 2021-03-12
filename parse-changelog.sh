#!/usr/bin/env bash

awk -v ver="$1" '
 /^## / { if (p) { exit }; if ($2 == ver) { p=1; next} } p
' "$2"

#awk -v version="$1" '/## / {printit = $3 == version}; printit;' "$2"