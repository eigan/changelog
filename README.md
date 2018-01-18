[![pipeline status](https://gitlab.com/eigan/changelog/badges/master/pipeline.svg)](https://gitlab.com/eigan/changelog/commits/master)
[![coverage report](https://gitlab.com/eigan/changelog/badges/master/coverage.svg)](https://gitlab.com/eigan/changelog/commits/master)


### Changelog generator

#### Manually adding a new changelog entry
Autocompletion is provided by git meta
```
$ changelog entry "My changelog entry"
Title [Fixes for authorization]: ...
Type [fix]: ...
Author [Einar]: ...

Changes:
1) All commits not in other branch
2) Manually add
3) None

Choose option [1]: 1
Choose branch [develop]: 

Found 8 commits

Preview: 
* [FIX] Fixes for authorization
  * Not able to login
  * Guest having super admin priviliges
  * Could not save settings
  
Save in changelogs/ as [eg-fix-authorization]: ...
Entry saved to changelogs/eg-fix-authorization.yml
```

#### Automatic with git commits
Can be executed to append to an existing entry
```
$ changelog generate:changes --since develop
Found 8 commits
Entry title [Awesome feature doing awesome stuff]: ...
Type [new]: ...
Author [Einar] ...

Preview:

* [NEW] Awesome feature doing awesome stuff (Einar)
  * Possible to ...
  * Can also do some
```

#### Append to CHANGELOG.md
```
$ changelog release "My new version"
Found 2 entries

Preview:

### My new version
* [NEW] Awesome feature doing awesome stuff (Einar)
  * Possible to ...
  * Can also do some

* [NEW] Mega feature for boat

* [FIX] Fixes for authorization
  * Not able to login
  * Guest having super admin priviliges
  * Could not save settings

* [FIX] General fixes of some bugs
```

#### TODO
 * Handle exceptions in commands
 * Autoresolve formatter
