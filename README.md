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
use Alone\EloquentSuperRelations\HasSuperRelations;

class Post extends Model
{

    use HasSuperRelations;

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function eagerLoadComments($relation,$models = [],$where = [])
    {
        // Get cached data for relation
        return null;
    }

}
```

## License

MIT