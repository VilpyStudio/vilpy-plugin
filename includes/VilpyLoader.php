<?php

/**
 * The class for loading and registering all the hooks
 */

namespace Vilpy;

class VilpyLoader
{
    protected $actions;

    protected $filters;

    //Collection of actions and filters to be registered
    public function __construct()
    {
        $this->actions = [];
        $this->filters = [];
    }

    //Adding action to collection
    public function addAction($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    //Adding filter to collection
    public function addFilter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    //Utility class to add hook to collection
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        //Add array of hooks to actions or filters array [][]
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    //Register all actions and filters
    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args']);
        }
        
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args']);
        }
    }
}
