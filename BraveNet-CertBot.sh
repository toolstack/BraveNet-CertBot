#!/bin/bash

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BNCB=$SCRIPT_DIR/BraveNet-CertBot.php

for domain in $RENEWED_DOMAINS; do
	php $BNCB $domain

#
# If you have a mix of BraveNet and other hosted domains you can use
# a case statement, like the example below, instead of the line above to
# limit the domains that the renewal script tries to update BraveNet with.
#
#	case $domain in
#	example.com)
#		php $BNCB $domain
#		;;
#	esac
done

