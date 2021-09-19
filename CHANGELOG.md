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
