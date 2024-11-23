<?php

abstract class Controller
{
    /**
     * The method that handles the logic for each action
     *
     * @param array $parameters
     * @return mixed
     */
    
    abstract public function handleAction(array $parameters = []);
}
