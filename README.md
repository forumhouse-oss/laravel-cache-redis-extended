Extended Redis Support for Laravel 4 & 5
=========================================

[![PHP version](https://badge.fury.io/ph/fhteam%2Flaravel-cache-redis-extended.png)](http://badge.fury.io/ph/fhteam%2Flaravel-cache-redis-extended) [![Laravel compatibility](https://img.shields.io/badge/laravel-4-green.svg)](http://laravel.com/) [![Laravel compatibility](https://img.shields.io/badge/laravel-5-green.svg)](http://laravel.com/) [![Code Climate](https://codeclimate.com/github/fhteam/laravel-cache-redis-extended/badges/gpa.svg)](https://codeclimate.com/github/fhteam/laravel-cache-redis-extended) [![Build Status](https://travis-ci.org/fhteam/laravel-cache-redis-extended.svg?branch=master)](https://travis-ci.org/fhteam/laravel-cache-redis-extended) [![Coverage Status](https://coveralls.io/repos/fhteam/laravel-cache-redis-extended/badge.svg?branch=master)](https://coveralls.io/r/fhteam/laravel-cache-redis-extended?branch=master)

Features:
-----------------------------------------

 - All `Cache` facade methods extended to accept arrays as keys to utilize Redis MULTI operations (multi-get, -set, -forget etc.)
 - Redis optimized commands used where appropriate (`EXISTS` in `has()`, `SET...NX` in `add()` etc)
 - :exclamation: No need to do `Cache::tags(...)` when reading data. `Cache::get()` is enough. 
 So you can now query cache without even knowing which tags are associated with some cache item
 - Tag operations optimized: there will be no second Redis query when doing `Cache::tags()` with the same tag set
 - Serialization support:
   - Built-in ability to serialize and deserialize Laravel models with all attributes and relations as a single 
   cache item (Laravel cannot cache models, only queries upon which models are constructed. Each relation in 
   Laravel currently is a separate query and thus a separate cache item) 
   - Ability to add new serializers / deserializers for custom object types
   
   
Architecture
-----------------------------------------
```
Core <===> Serialization <===> Encoding
 |                                 | 
 |---->  TagVersionStorage  <------|
 
          + Utility +
```

 - **Core** contains mostly Redis command implementations
 - **Serialization** handles packing and unpacking cache items into a structure, suitable to be placed into a cache
 (encoded item value, expiration data, item tags with their versions). Serialization relies on Coders to convert objects into 
 something easily serializable.
   - **Coders** contain low-level serialization routes. They receive data (objects or whatever) and emit something, that
   can be passed to PHP's `serialize()` in order to get encoded object's representation. Every coder must implement 
   `CoderInterface`
 - **TagVersionStorage** is a per-Redis-connection singleton, that manages tag versions: fetches actual tag versions, 
 compares them, flushes them etc. All tag storage handlers should implement `TagVersionStorageInterface`. 
   - `PlainTagVersionStorage` is a basic version of tag version storage that doesn't share any information about tag 
   versions outside current PHP process. You can implement a version, that will store actual tag versions,
    for example, in APC if querying Redis becomes expensive or just needs to be avoided.
 - **Utility** contains some low-level specific tools which are not subsystem related.
   - `RedisConnectionTrait` - is a trait, that allows to reuse the same scheme of Redis connection handling across the
   project
   - `Arr` contains some low-level array routines
   - `Time` contains some time management routines

Contribute
-----------------------------------------

 - Code style - Symfony 2
 - Use type hinting where possible
 - Use phpdoc annotations where possible
 - Prefer language constructs to strings (for example, use `MyClass::class`, not `'MyClass'` where class name is needed)