#!/bin/bash
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  TARGET="$(readlink "$SOURCE")"
  if [[ $TARGET == /* ]]; then
    echo "SOURCE '$SOURCE' is an absolute symlink to '$TARGET'"
    SOURCE="$TARGET"
  else
    DIR="$( dirname "$SOURCE" )"
    echo "SOURCE '$SOURCE' is a relative symlink to '$TARGET' (relative to '$DIR')"
    SOURCE="$DIR/$TARGET" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
  fi
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
DIR=$DIR/../Logs
DUBLICATEDIR=$DIR/../Logs/Duplicate
file=logs-$(date --date="now" +%Y-%m).zip
find $DIR -type f   -not -newermt $(date --date="now" +%Y-%m-01) -print | zip -r $DIR/$file -@
find $DIR -type f   -not -newermt $(date --date="now" +%Y-%m-01) -print -delete
find $DUBLICATEDIR -type f   -not -newermt $(date --date="now" +%Y-%m-01) -print -delete