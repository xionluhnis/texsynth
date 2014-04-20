#!/usr/bin/env bash

USER="$(whoami)"
GROUP=apache

function cache() {
  local dir="$1"
  [[ ! -d "$dir/images" ]] && return
  pushd "$dir" # go into directory

  echo
  echo "### Caching images in $dir"
  echo
  shopt -s globstar # enable **/*.png
  for img in ./**/*.png; do
    cached="./cache/${img/.png/.jpg}"
    if [[ ! -f "$cached" ]] || [[ ! $(file "$cached" | grep image) ]]; then
      echo "Caching $dir/$img"
      cache_dir=$(dirname "$cached")
      if [[ ! -d "$cache_dir" ]]; then
        mkdir -p "$cache_dir"
        chown -R "$USER:$GROUP" cache
      fi
      convert "$img" "$cached"
    fi
  done

  popd # get out of directory
}

if [[ $# -eq 0 ]]; then
  # for each gallery
  for dir in [0-9][0-9][0-9][0-9]/*/; do
    cache "$dir"
  done
else
  for dir in $@; do
    cache "$dir"
  done
fi
