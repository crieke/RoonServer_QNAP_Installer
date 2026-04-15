#!/bin/sh
case "$1" in
  "RoonServer") echo $ROON_QNAP_MNT_DIR
  ;;
  "system") echo $QNAP_QTS_VER
  ;;
esac
