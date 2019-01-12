namespace App\UserSearch;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ChannelPriceSearch
{
   const MODEL = App\Shop\ChannelPrices\ChannelPrice;

    public static function apply(Request $filters)
    {
        $query = 
            static::applyDecoratorsFromRequest(
                $filters, (new User)->newQuery()
            );

        return static::getResults($query);
    }
}
