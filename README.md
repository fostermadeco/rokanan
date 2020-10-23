# Rokanan

> “Hail Rokanan, my guest. Tell me why you go south.”
>
> “I go to find my enemy, Lady. I hope to enter their . . . their castle,
> and make use of their . . . message-sender, to tell the League they are
> here.”
>
> — “Rocannon’s World”,  Ursula K. Le Guin 

Rokanan is a tool to simplify provisioning standard development environments across teams. It is effectively a wrapper for Vagrant and, as its eponym might suggest, Ansible.

## Prerequisites

* PHP 7.1.3
* [Composer](https://getcomposer.org/)

## Installation

Install Rokanan into your project with 

```
composer require --dev fostermadeco/rokanan dev-master --ignore-platform-reqs
``` 

To facilitate invoking the locally installed `rokanan`, prepend `./vendor/bin` to your `PATH`.

NB: Global installation is no longer supported. 

### Note on Homebrew

Because [Homebrew/php](https://github.com/Homebrew/homebrew-php) was deprecated earlier this year and migrated to [Homebrew Core](https://github.com/Homebrew/homebrew-core), you may also experience inconsistencies or unexpected behavior with PHP on your host machine if you have made any changes to the brew-installed PHP since March. It may be advisable at your discretion to thoroughly [uninstall Homebrew](https://docs.brew.sh/FAQ#how-do-i-uninstall-homebrew) and [reinstall](https://docs.brew.sh/Installation) it — more than one of us has done this without adverse effects.

If you do uninstall it, you may also want to follow the uninstaller’s advice and delete any or all of the non-empty directories that the uninstaller lists upon completion. The _one exception_ is `/usr/local/etc` unless you do not have any self-signed certs stored in `/usr/local/etc/ssl/certs`.

### Note on GNU coreutils

If you have GNU coreutils installed through Homebrew (or otherwise), you may occasionally see the following warning:

```
stty: standard input: unable to perform all requested operations
``` 

You can safely ignore it. If it truly bothers you, pick a folder in your `PATH` that has precedence over `/usr/local/opt/coreutils/libexec/gnubin`. In that folder, create a symlink to /bin/stty.

## Features   

The primary feature while Rokanan is still in beta is that it provides access to custom [Ansible roles](https://github.com/fostermadeco/ansible-roles) so we can avoid the confusion and complexity of including them as a Git submodule in projects.

There are also a couple of beta-release commands you can run — your feedback on their functionality is welcome and desired!

### `rokanan init`

This will help you initialize a project interactively.

### `rokanan trust-cert`

Run this after `vagrant up` to automatically trust the self-signed cert (only supported on macOS).

### `rokanan check`

This will report whether or not your system is optimized for use with Rokanan.

### `rokanan connect`

This is effectively a wrapper around `vagrant ssh`. All wrapped vagrant commands automatically set the `VAGRANT_USE_VAGRANT_TRIGGERS=1` environment variable to suppress the 2.1+ warning regarding the vagrant-triggers plugin vs core functionality.

### `rokanan run [subcommand]`

This will run a command inside the provisioned VM without creating a session. The command will be run in the project directory (`/var/www/{{ hostname }}`).
