<?php
/**
 * fzDoctrinePagedRoute represents a route that is bound to a doctrine class and Pager.
 * It doesn't extends sfDoctrineRoute nor sfObjectRoute, becouse we don't want to
 * create models object as it. We do want to get it's list for Pager however.
 *
 * @author fizyk
 */
class fzDoctrinePagedRoute extends sfRoute
{
    private $pager;
    private $items_per_page = 0;

    /**
     * fzDoctrinePagedRoute represents a route that is bound to a doctrine class and Pager.
     * It doesn't extends sfDoctrineRoute nor sfObjectRoute, becouse we don't want to
     * create models object as it. We do want to get it's list for Pager however.
     * aditionaly to parent's class, required options are:<br/>
     * <b>model:</b> model name used on given page<br/>
     * <b>browse_method:</b> method used to get objects for list (for pager)<br/>
     * <b>per_page:</b> number of items per page, or setting name from app.yml with that number
     * @param string $pattern
     * @param array $defaults
     * @param array $requirements
     * @param array $options
     */
    public function __construct( $pattern, array $defaults, array $requirements, array $options )
    {
        //parent's construction, we just add our stuff below
        parent::__construct( $pattern, $defaults, $requirements, $options );

        //Checking requirements below:
        if( !isset( $this->options['model'] ) )
        {
            throw new InvalidArgumentException(sprintf('You must pass a "model" option to %s ("%s" route)', get_class($this), $pattern ) );
        }
        if( !isset( $this->options['browse_method'] ) )
        {
            throw new InvalidArgumentException(sprintf('You must pass a "browse_method" option to %s ("%s" route)', get_class($this), $pattern ) );
        }
        if( !isset( $this->options['per_page'] ) )
        {
            if( null === ( $this->options['per_page'] = sfConfig::get( 'app_fzDoctrinePagedRoutePlugin_per_page' , null ) ) )
            {
                throw new InvalidArgumentException( 
                        sprintf('You must pass a "per_page" option to %s ("%s" route), or set app_fzDoctrinePagedRoutePlugin_per_page setting in your app.yml file!',
                                get_class($this), $pattern ) );
            }
        }
    }

    /**
     *
     * @param mixed $url
     * @param array $context
     * @return boolean
     * false if there's no match (or page number too far), otherwise an array of parameters
     */
    public function matchesUrl($url, $context = array())
    {
        //Parent's match first:
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
            return false;
        }
        //setting default page value of 1, because it can be
        if( !isset( $parameters['page'] ) )
        {
            $parameters['page'] = 1;
        }
        //Getting page number:
        if( is_numeric( $this->options['per_page'] ) )
        {
            $this->items_per_page = $this->options['per_page'];
        }
        else
        {
           if(! is_integer( $this->items_per_page = sfConfig::get( $this->options['per_page'] ) ) )
           {
               throw new InvalidArgumentException( 'Your per_page option/setting must provide integer value!' );
           }
        }

        //getting browse_method name
        $browse_method = $this->options['browse_method'];

        //creating pager
        $this->pager = new sfDoctrinePager( $this->options['model'] , $this->items_per_page );
        $this->pager->setQuery( Doctrine::getTable( $this->options['model'] )->$browse_method() );
        $this->pager->setPage( $parameters['page'] );
        $this->pager->init();

        //Obviously, if page is out of reach, we shouldn't generate it
        if( $this->pager->getLastPage() < $parameters['page'] || $parameters['page'] < 1 )
        {
            return false;
        }
        
        return $parameters;
    }

    /**
     * Returns sfDoctrinePager object used in this route, same way the getObject from sfObjectRoute, becouse of DRY rule.
     * @return sfDoctrinePager
     */
    public function getPager()
    {
        return $this->pager;
    }

}
?>
