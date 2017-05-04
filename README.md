# BraveNet Support for CertBot

This script works with CertBot's `--renew-hook` option to automatically update BraveNet's certificate list with the new Let's Encrypt cert.

It accomplishes this by a scripted web session to the BraveNet administrative site.

## Requirements

1. A host with shell access you can run cron jobs on as root
2. CertBot
3. PHP (available via the command line) with MCRYPT

## Installation

1. Install [CertBot](https://certbot.eff.org/).
2. Clone the script repo to somewhere on the system that you run CertBot and that CertBot has access too.
3. Copy and edit the `BraveNet-CertBot.ini-example` file, adding your BraveNet user name and password.
4. Run `php BraveNet-CertBot.php`, which will update the ini file with an encrypted version of your password.
5. Configure CertBot to run daily using the `--renewal` mode and then add the following option: `--renew-hook /path/to/BraveNet-CertBot/BraveNet-CertBot.sh`.

## Usage

WARNING: Since this script is basically a screen scraper it is prone to breakage if BraveNet decides to change their admin site.  

WARNING: This script must know your username/password to connect to BraveNet with, it stores these in the ini file and "encrypts" your password so as to obfscate it.  However it is easily recoverable and the secruity of the ini file should be set such that only the user that is running CertBot (usually root) has access to it.

The script is intended to be run as part of CertBot's renewal mode, it takes only one parameter, the domain to update.

You can run it manually and it will update the BraveNet certificate with whatever one is currently in the CertBot store (/etc/letsencrypt/active) for the passed in domain name.

## CertBot with multiple hosting providers

The current script assumes all of your CertBot certificates are for BraveNet hosted domains, this may not be the case so you can change the `BraveNet-CertBot.sh` script to only update your BraveNet domains.

To do so, load the script in to an editor, line 8 will be:

`	php $BNCB $domain`

Comment this out like so:

`#	php $BNCB $domain`

Then uncomment the block of lines from line 15 to 19:

`
	case $domain in
	example.com)
		php $BNCB $domain
		;;
	esac
`

Change line 16, `example.com)`, to be whatever domain you want to update (make sure to include the closing bracket, it's not a typo).

If you have mulitple domains you want to update, copy lines 15 to 18 and update the domain name as required:

`
	case $domain in
	example.com)
		php $BNCB $domain
		;;
	case $domain in
	secondexample.com)
		php $BNCB $domain
		;;
	esac
`
## CertBot with multiple BraveNet accounts

If you have multiple BraveNet accounts, you'll need to have multiple copies of the script available to support them.

So for example, you could create a copy of the script in "BNCB-Account1" and a second copy in "BNCB-Account2" and edit the ini file with each account details.

Then, following the example above, change the shell script in one used on the command line of CertBot to call the script from the appropriate directory for each domain.

`
	case $domain in
	example.com)
		php /path/to/script/BNCB-Account1/BraveNet-CertBot.php $domain
		;;
	case $domain in
	secondexample.com)
		php /path/to/script/BNCB-Account2/BraveNet-CertBot.php $domain
		;;
	esac
`
