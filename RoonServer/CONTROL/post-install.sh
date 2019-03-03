#!/bin/sh

APKG_PKG_DIR=/usr/local/AppCentral/RoonServer

case "$APKG_PKG_STATUS" in

	install)
		# post install script here
		/bin/mkdir "$APKG_PKG_DIR/tmp"
		/bin/mkdir "$APKG_PKG_DIR/etc"
		
		;;
	upgrade)
		# post upgrade script here (restore data)
		# cp -af $APKG_TEMP_DIR/* $APKG_PKG_DIR/etc/.
		mv $APKG_TEMP_DIR/RoonServer.conf $APKG_PKG_DIR/etc/RoonServer.conf

		;;
	*)
		;;

esac

exit 0
