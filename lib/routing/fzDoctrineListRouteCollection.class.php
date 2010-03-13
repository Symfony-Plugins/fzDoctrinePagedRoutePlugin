<?php
/**
 *
 * fzListRouteCollection generates three routes for modules: default, paged and show.
 * It doesn't follow CRUD, as it's not always necessary to create CRUD route set for some lists,
 * but still great to have them generated.<br/>
 * 
 * @author fizyk
 */
class fzDoctrineListRouteCollection extends sfRouteCollection
{
    private $defaults;

    /**
     * fzListRouteCollection generates three routes for modules: default, paged and show.
     * It doesn't follow CRUD, as it's not always necessary to create CRUD route set for some lists,
     * but still great to have them generated.<br/>
     * It utilises those options:<br/>
     * <b>model:</b> name of the model used in given list<br/>
     * <b>type:</b> type of the model, typically: object<br/>
     * <b>page_indicator:</b> name for another pages used like that: /prefix/page_indicator/[page_number]<br/>
     * <b>show_pattern:</b> usual pattern used for show, default is :id<br/><br/>
     * This colelction uses fzDoctrinePagedRoute for default and paged routes,
     * so be sure to include it's required options as well.
     * @param array $options
     */
    public function  __construct( array $options )
    {
        parent::__construct($options);

        $this->checkOptions();
        $this->setDefaults();
        $this->generateRoutes();
    }


    private function checkOptions()
    {
        //If prefix path's not set, we'll use Collection's name
        if( !isset( $this->options['prefix_path'] ) )
        {
            $this->options['prefix_path'] = $this->options[ 'name' ];
        }
        
        //Default action will be index as well
        if( !isset( $this->options['action']) )
        {
            $this->options['action'] = 'index';
        }

        //Default show_pattern will be page
        if( !isset( $this->options['page_indicator'] ) )
        {
            if( null === ( $this->options['page_indicator'] = 
                    sfConfig::get( 'app_fzDoctrinePagedRoutePlugin_page_indicator', null) ) )
            {
                $this->options['page_indicator'] = 'page';
            }
        }

        //Default page indicator will be :id
        if( !isset( $this->options['show_pattern'] ) )
        {
            $this->options['show_pattern'] = ':id';
        }

        //Default module will be set as route name.
        if( !isset( $this->options['module'] ) )
        {
            $this->options['module'] = $this->options['name'];
        }
        
    }

    private function setDefaults()
    {
        $this->defaults = array(
           'module' => $this->options['module'],
           'action' => $this->options['action'],
           'sf_format' => 'html'
        );
    }
    
    protected function generateRoutes()
    {
        $this->routes[ $this->options['name'] ] = $this->getDefaultRoute();
        $this->routes[ $this->options['name'].'_paged' ] = $this->getPagedRoute();
        $this->routes[ $this->options['name'].'_show' ] = $this->getShowRoute();
    }

    private function getDefaultRoute()
    {
        $pattern = sprintf(
           '%s.:sf_format',
           $this->options['prefix_path']
        );
        $requirements = array('sf_method' => 'get');
        return new fzDoctrinePagedRoute($pattern, $this->defaults, $requirements, $this->options);
    }

    private function getPagedRoute()
    {
        $pattern = sprintf(
           '%s/%s/%s',
           $this->options['prefix_path'],
           $this->options['page_indicator'],
           ':page'
        );
        $requirements = array('sf_method' => 'get');
        return new fzDoctrinePagedRoute($pattern, $this->defaults, $requirements, $this->options);
    }

    public function getShowRoute()
    {
        $pattern = sprintf(
           '%s/%s',
           $this->options['prefix_path'],
           $this->options['show_pattern']
        );

        $defaults = array(
           'module' => $this->options['module'],
           'action' => 'show'
        );
        $requirements = array(
            'sf_method' => 'get',
            'id' => '\d+'
        );
        
        return new sfDoctrineRoute( $pattern, $defaults, $requirements, $this->options );
    }
}
?>
