# GitLab-Composer-Bridge

This is a simple bridge to let work composer with self hosted GitLab repositories. A very simple packagist replacement.

## Installation

Clone this repository to a folder of your choice

```bash
git clone https://github.com/Messier-1001/GitLab-Composer-Bridge.git /youre/folder
```

Configure a apache vhost that points to the `web` sub folder as document root.

Ensure the `cache` sub folder is writable for current web server user (often used `www-data:www-data`)

Copy/rename the file `gitlab-dev.json` inside the `config` sub folder to `gitlab.json`.

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
   
### ! Important !

To use the GitLab-Composer-Bridge the GitLab project must be defined inside a group.

First create a group that represents the vendor of a package. e.g. if the composer package
is `federchen-frost/frost.core` the vendor is `federchen-frost` and the GitLab group name must be also `federchen-frost`

Then add your projects/repositories to the group (`frost.core` for example).
If the project is ready developed add a tag to the project that gives it a version number. (e.g. `0.1.0`)

The group and the project name can only contain the following chars `a-z0-9._-` (only lower case letters!)
 
This restrictions are given by composer.
   
After this steps you can test it by calling `http://your-bridge-host/packages.json`

It must show the JSON that represents all available projects with an valid `composer.json`

### Use the packages with your local composer

Its easy to use with your composer.

Create the `composer.json` by your needs. After it add the GitLab-Composer-Bridge url `http://your-bridge-host/`
as a repository to the `composer.json` like this

```json
   "repositories": [
      {
         "type": "composer",
         "url": "http://your-bridge-host/"
      }
   ],
```

After it you can now require your projects like

```json
   "require": {
      "php": ">=7.1",
      "federchen-frost/frost.core": "*"
   }
```

inside the same `composer.json`

If you only will use a HTTP and no SSL (https) repository URL you must define it by
 
```json
   "config": {
      "secure-http": false
   },
```

### Example composer.json

```json
{
   "name": "federchen-frost/frost.translation",
   "type": "library",
   "description": "A PHP translation library.",
   "support": {
      "issues": "http://my-gitlab/federchen-frost/frost.translation/issues",
      "source": "http://my-gitlab/federchen-frost/frost.translation"
   },
   "license": "MIT",
   "config": {
      "secure-http": false
   },
   "authors": [
      {
         "name": "John Who",
         "email": "john.who@example.com"
      }
   ],
   "repositories": [
      {
         "type": "composer",
         "url": "http://your-bridge-host/"
      }
   ],
   "require": {
      "php": ">=7.1",
      "federchen-frost/frost.core": "*"
   },
   "autoload": {
      "psr-4": {
         "Frost\\Translation\\": "src/"
      }
   }
}
```

If you run `composer install` the repository will be resolved and used

## Extended configuration

GitLab can call an URL on each change in a repository/project. This can be used to trigger a reload of all data from
GitLab.

This can be configured.

* Login to GitLab. Login as the admin user, also configured for GitLab API usage.
* Click to the `wrench` symbol (`"Admin area"`) at the top right from GitLab page
* Click `System Hooks`
* The **url** must be `http://<your-bridge-host>/trigger-reload.php`
* As **Secret Token** `GITLAB_COMPOSER_BRIDGE_RELOAD` must be inserted
* Ensure the `Push events`, `Tag push events` and `Enable SSL verification` checkboxes are **checked**.
* Click the `Add System Hook` button.

From now, on each change inside all repositories, the GitLab-Composer-Bridge will be informed about a change. On next
composer request to packages.json an reload is triggered.

If you want it more specific, you can assign the system hook to one or more groups (namespaces) inside GitLab.

Have fun :-)