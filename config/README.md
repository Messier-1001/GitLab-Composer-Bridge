# GitHub-Composer-Bridge Configuration

Before you start with this tool, you have to configure it.

Copy/rename the file `gitlab-dev.json` inside this folder to `gitlab.json`.

Then open the file `gitlab.json` with your favorite editor and place the required info inside.

You have to configure the following things

* **`apiUrl`** (`string`):
  Here you have to place the URL that should be called to connect the API of your GitLab installation.
  Simple replace the <host> part inside the following URL with your host and maybe with a port definition:
  `http://<host>/api/v4`
* **`apiKey`** (`string`):
  This is your private API key token. I prefer to use the API key of an admin user. Otherwise only the projects, visible
  for defined user are usable. You can find the API key by `Profile` > `Edit the profile` > `Account` > `Private token`
  inside your GitLab installation.
* **`method`** (`string`):
  This is the Method, used to share the GitLab repository URL with composer. Composer will use the method, defined here,
  to checkout a repository from GitLab. There are only 2 valid methods:
   * `http`: A simple http address like `http://example.com/vendor/package.git`
   * `ssl`:  A git protocol URL like `git@example.com:vendor/package.git`
* **`caseless`** (`boolean`): (since v0.2)
  Should also GitLab projects be usable as composer packages, using upper case letters? If so all upper case letters
  inside group name and project name will be converted to lower case. To be usable the composer.json must declare the
  lower case version as `vendor/package-name` as `name` property. For example `Foo/Blub.Bar` must be defined inside the
  `composer.json` as `foo/blub.bar` for property `name`.