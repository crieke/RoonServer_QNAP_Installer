#!/bin/sh
export CONF=/etc/config/qpkg.conf
export QPKG_NAME="RoonServer"
export QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`

## Remove old symlinks
rm -f "${QPKG_ROOT}"/mnt/*

## Create symlimks initially in Roon's mnt folder
find /share -maxdepth 1 -type l -print | while read x; do
   SYMLINK_DEST="${QPKG_ROOT}/mnt/"$(basename "$x")
   ln -sf "$x" "$SYMLINK_DEST"
done

## watch /share directory for changes
inotifywait -m -e create,delete /share | while read xx; do 
   L="$(echo -n "$xx" | sed 's/^[^ ]* //')"
   CMD="$(echo "$L" | sed 's/ .*//')"
   FILE="$(echo "$L" | sed 's/^[^ ]* //')"
   if [[ $CMD == "CREATE" ]]; then
      ln -sf "/share/$FILE" "${QPKG_ROOT}"/mnt/
      echo "Linking $FILE..."
   elif [[ $CMD == "DELETE" ]]; then
      rm -f "${QPKG_ROOT}/mnt/$FILE"
      echo "Removing $FILE..."
   fi
done
