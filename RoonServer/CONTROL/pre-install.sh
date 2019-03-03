#!/bin/sh

APKG_PKG_DIR=/usr/local/AppCentral/RoonServer

case "$APKG_PKG_STATUS" in

	install)
		# pre install script here
		# installing bash over entware
		/usr/local/AppCentral/entware/opt/bin/opkg install bash
		ln -sf /usr/local/AppCentral/entware/opt/bin/bash /bin/bash
		;;
	upgrade)
		# pre upgrade script here (backup data)
		cp $APKG_PKG_DIR/etc/RoonServer.conf $APKG_TEMP_DIR/RoonServer.conf
		;;
	*)
		;;

esac

exit 0
