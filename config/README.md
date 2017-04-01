# GitHub-Composer-Bridge Configuration

Before you start with this tool, you have to configure it.

Copy/rename the file `gitlab-dev.json` inside this folder to `gitlab.json`.

Then open the file `gitlab.json` with your favorite editor and place the required info inside.

You have to configure the following things

* **`apiUrl`**: Here you have to place the URL that should be called to connect the API of your GitLab installation.
             Simple replace the <host> part inside the following URL with your host and maybe with a port definition:
             `http://<host>/api/v4`
* **`apiKey`**: This is your private API key token. I prefer to use the API key of an admin user. Otherwise only the
             projects, visible for defined user are usable. You can find the API key by
             `Profile` > `Edit the profile` > `Account` > `Private token` inside your GitLab installation.
* **`method`**: This is the Method, used to share the GitLab repository URL with composer
             Composer will use the method, defined here, to checkout a repository from GitLab.
             There are only 2 valid methods
   * `http`: A simple http address like `http://example.com/vendor/package.git`
   * `ssl`:  A git protocol URL like `git@example.com:vendor/package.git`