This page will describe how to setup development environment for productive work with CleverStyle CMS and contribute back to project.

### IDE
You can, of course, use any IDE of your choice, but system is developed under PhpStorm (usually EAP version), so this is recommended IDE that will boost your productivity a lot.
Repository already contains code style config and inspections settings for PhpStorm, so you'll have right settings for these key elements from the beginning.

### Interpreter
PHP 5.5 is minimum requirement, also HHVM is supported, so you should have any of those or both

### Web Server
Apache2 and Nginx are officially supported, so it is recommended to have one of those or both

### DB
The only DB supported out-of-the-box is MySQL/MariaDB, so you'll need it as well

### OS
Primary development goes under Linux-based OS, Windows and OS X should work as well, but might have some limitation or weirdness (like non-latin translations files names under Windows).

### Other tools
It is recommended to configure file watchers in PhpStorm (Tools->File Watchers) for SCSS, LiveScript and Jade files (obviously, you'll need those cli tools installed in your system).
After this any editing of SCSS, LiveScript and Jade files will automatically compile them to regular CSS, JS and HTML files.
You are not forced to use these tools, but you'll need them when modifying system core (for your needs or as bug fixes).

### Development edition of CleverStyle CMS
NOTE: this section is relevant for development of system core, you can install regular stable version otherwise

At first fork repository on GitHub to your own account.
Then prepare virtual host for future website on your local machine.
When virtual host is ready clone repository from your account to virtual host directory:
```
git clone git@github.com:username/CleverStyle-CMS.git
```
Then add remote `upstream` repository, so that you'll have quick access to latest upstream code:
```
git remote add upstream git@github.com:nazar-pc/CleverStyle-CMS.git
```

[Now build distributive](/docs/Installer-builder)

[Now install system](/docs/Installation) to the same directory

Afterwards run:
```
git reset --hard HEAD
```

Now you can open this directory in IDE.

### Update you system
NOTE: this section is relevant for development of system core, you can make regular update to stable version through administration UI otherwise

Generally development version is updated very often, so you need to compare `upstream/master` with you local branch, analyse changes, apply some manual changes if needed and checkout `upstream/master` as current local branch.

### Contributing back to system core
NOTE: this section is relevant for development of system core

Before making any changes create separate git branch from `upstream/master` (do not forget to fetch changes before) locally, give it name that can be easily associated with what you're planning to do.
After you did some changes, make commit with short description that explains what you just did.
Feel free to make few commits if they touch different part of system, but do not mix completely different things in one branch.
When all changes done push local branch to your GitHub repository.
Go to GitHub and you'll see suggestion to make pull request.
In pull request add extensive description for your changes so that other developers can understand your intentions.
Wait when pull request will be accepted.

If you found some issue in your changes - feel free to push more commits to the same branch, no need to create new pull request.
