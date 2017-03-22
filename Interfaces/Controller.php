<?php
interface Controller {
    /**
     * Returns the code to render.
     * @return string Code to render.
     */
    public function render();
}