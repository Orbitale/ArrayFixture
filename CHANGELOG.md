# v1.3.6

* Ensure PHP 8.4 compatibility
* Add doctrine/persistence 4.0 compatibility
* Add doctrine/data-fixtures 2.0 compatibility

# v1.3.5

* Ensure PHP 8.3 compatibility
* Add doctrine/orm 3.0 compatibility
* Test over doctrine orm 2 and 3

# v1.3.4

* Allow using doctrine/instantiator ^2.0
* Fix a test with latest DataFixtures versions

# v1.3.3

* Allow using doctrine/persistence ^3.0

# v1.3.2

* Test the package on PHP 8.1
* Fix phpstan issues
* Migrate to phpstan 1.0
* Fix PHPUnit's XSD path and use local one

# v1.3.1

* Fix compatibility with Doctrine ODM

# v1.3.0

* Remove php 7.2 support
* Update wrongly license after latest release (I updated the LICENSE file but forgot to update it in Composer, bly me).
* Refresh & clean the test suite with latest PHPUnit version

# v1.2.0

* Change license from AGPL-3.0 to LGPL-2.1

# v1.1.1

* Widen `doctrine/persistence` version dependency.

# v1.1.0

* Removed `OrderedFixtureInterface` implementation, because it conflicts with `DependentFixtureInterface`.
* Deprecated `getOrder` method: it has to be implemented manually if needed.

# v1.0.2

* Instead of PHP 7.4, now supporting 7.3, 7.2 and 8.0 as well.
* Refactored entire CI to make tests on all OSes and supported PHP versions.

# v1.0.1

* Fixed `ArrayFixture::getObjects()` return type's documentation to `array[]` instead of `object[]`
* Added `phpstan` rules to add some code quality documentation and typing.

# v1.0.0

First release
