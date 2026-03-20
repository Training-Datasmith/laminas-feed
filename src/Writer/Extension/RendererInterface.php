<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension;

use Dom_Document;
use Dom_Element;
interface Renderer_Interface
{
    /**
     * Set the data container
     *
     * @param  mixed $container
     * @return void
     */
    public function set_data_container($container);
    /**
     * Retrieve container
     *
     * @return mixed
     */
    public function get_data_container();
    /**
     * Set DOMDocument and DOMElement on which to operate
     *
     * @return void
     */
    public function set_dom_document(Dom_Document $dom, Dom_Element $base);
    /**
     * Render
     *
     * @return void
     */
    public function render();
}