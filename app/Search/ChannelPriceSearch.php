namespace App\UserSearch;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ChannelPriceSearch implements Filter
{
   use \App\Traits\SearchableTrait;
   const MODEL = App\Shop\ChannelPrices\ChannelPrice;

    public static function apply(Request $filters)
    {
        $query = 
            static::applyDecoratorsFromRequest(
                $filters, (new \App\Shop\ChannelPrices\ChannelPrice)->newQuery()
            );

        return static::getResults($query);
    }
}
