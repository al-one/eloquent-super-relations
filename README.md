# Laravel Eloquent Super Relations

## Installing

```
# composer.json

"minimum-stability": "dev",
"prefer-stable": true,
```

```sh
$ composer require al-one/eloquent-super-relations -vvv
```

## Usage

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Alone\EloquentSuperRelations\HasSuperRelations;

class User extends Model
{

    use HasSuperRelations;

    public function profile()
    {
        return $this->hasOne('App\Profile', 'uid');
    }

    /**
     * @return  Model|Collection|array|null
     */
    public function eagerLoadProfile($relation, $models = [], $where = [])
    {
        // Get cached data for relation
        if(!empty($where['uid'])) {
            return cache()->remember("user:profile:{$where['uid']}", 86400, function() use($where) {
                return Profile::find($where['uid']);
            });
        }
        // return null for get from database
        return null;
    }

}
```

## License

MIT