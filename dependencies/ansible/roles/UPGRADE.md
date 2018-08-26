# Upgrade Notes

Like a Composer or Node package, the `roles` submodule should only be updated intentionally to take advantage of bug fixes or new features.

When upgrading, please take the following into consideration.

## `official-xenial-box`

When setting the submodule to a commit more recent than the `feature/official-xenial-box` merge, create a file called `version` inside the `ansible` directory to preserve the existing box version. If you wish to convert a project from using `bento/ubuntu-16.04` to using the official Xenial box, it is best to destroy your Vagrant environment and rebuild it. Be sure to add a note to the project `README` so your colleagues know to do the same.
