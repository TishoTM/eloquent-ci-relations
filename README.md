# Eloquent case-insensitive relations

<p align="left">
<a href="https://travis-ci.org/TishoTM/eloquent-ci-relations"><img src="https://api.travis-ci.org/TishoTM/eloquent-ci-relations.svg?branch=master" alt="Build Status" /></a>
<a href="https://packagist.org/packages/tishotm/eloquent-ci-relations"><img class="badge" src="https://poser.pugx.org/tishotm/eloquent-ci-relations/version" alt="Version" /></a>
<a href="https://packagist.org/packages/tishotm/eloquent-ci-relations"><img class="badge" src="https://poser.pugx.org/tishotm/eloquent-ci-relations/downloads" alt="Total Downloads" /></a>
<a href="https://packagist.org/packages/tishotm/eloquent-ci-relations"><img class="badge" src="https://poser.pugx.org/tishotm/eloquent-ci-relations/license" alt="License" /></a>
</p>

Using Eloquent out of the box on case-insensitive collation databases could potentially return incomplete collection of items
if the foreign keys are set as strings and they differ in terms of uppercase vs lowercase.
On eager loaded relations Eloquent builds a dictionary of the parent models and associates their related models by their keys.
PHP is case-sensitive and therefore if the keys are different, then some of the related models will not be returned.
This package simply normalizes the dictionary keys into lowercase.

Example:

**Items table**

| uuid | name |
| --- | --- |
| aaa | First |
| bbb | Second |
| ccc | Third |

**Tags table**

| id | label |
| --- | --- |
| 1 | tag 1 |
| 2 | tag 2 |
| 3 | tag 3 |

**item_tag table**

| item_uuid | tag_id |
| --- | --- |
| AAA | 1 |
| aaa | 2 |
| bbb | 3 |

`Item::with('tags')->find('aaa');`

The related tags would include only the "tag 2" even if the the DB collation is case-insensitive and the returned query data includes "tag 1".

**With case insensitive relations:** The related tags would include both "tag 1" and "tag 2".

## Requirements

- illuminate/database 5.5.33 and up, 5.6.\*, 5.7.\*, 5.8.\*, 6.20.26, 7.\*, 8.\*

## Installation

`composer require tishotm/eloquent-ci-relations`

## Usage

```PHP

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;

    ... relations
}
```

## License

[MIT license](https://opensource.org/licenses/MIT).
