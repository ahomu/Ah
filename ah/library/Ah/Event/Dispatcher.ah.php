<?php
/**
 * Ah_Event_Dispatcher provides object's communication method while application sequence.
 * ( reference: Symfony Component "sfEventDispatcher" )
 *
 * @package     Ah
 * @subpackage  Event
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Event_Dispatcher
{
    private
        $_listeners  = array();

    /**
     * listen
     *
     * @param  $event
     * @param  $callable
     * @return void
     */
    public function listen($event, $callable, $params = array())
    {
        if ( empty($this->_listeners[$event]) ) {
            $this->_listeners[$event] = array();
        }

        $this->_listeners[$event][] = $callable;
    }

    /**
     * unlisten
     *
     * @param  $event
     * @param  $callable
     * @return void
     */
    public function unlisten($event, $callable)
    {
        foreach ( $this->getListeners($event) as $p => $listener ) {
            if ( $listener === $callable ) {
                unset($this->_listeners[$event][$p]);
            }
        }
    }

    /**
     * getListeners
     *
     * @param  $event
     * @return $callables
     */
    public function getListeners($event)
    {
        return $this->_listeners[$event];
    }

    /**
     * notify
     *
     * @param  $event
     * @return void
     */
    public function notify($subject, $event)
    {
        $this->_update($this->getListeners($event), $subject);
    }

    /**
     * update
     *
     * @param  $callables
     * @return void
     */
    private function _update($callables, $subject)
    {
        foreach ( $callables as $callable ) {
            call_user_func($callable, $subject);
        }
    }
}
