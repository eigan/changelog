## [0.16] - 2018-03-03
### Changed
- Dont ask if it is ok to write, just write.

## [0.15] - 2018-02-03
### Added
- Let the formatters suggest entry types

### Removed
- Removed Entry::setTitle and Entry::setType

### Fixed
- Add author to KeepAChangelogFormatter (Edvin Hultberg)

## [0.14] - 2018-01-28
### Added
- Added Keep A Changelog formatter
- Override entriespath and formatter via variables in CHANGELOG.md
- Suggest unique entry filename if collision

### Removed
- The remote feature with gitlab (parse merge requests, references)

### Fixed
- Cleanup code a bit. Making sure we pass phpstan level 7
- Fix sorting of changelog entries

<!--
formatter: keep-a-changelog
-->
