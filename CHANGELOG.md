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
