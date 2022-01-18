<?php
namespace booosta\ui_datepicker;

\booosta\Framework::add_module_trait('webapp', 'ui_datepicker\webapp');

trait webapp
{
  protected function preparse_ui_datepicker()
  {
    $libpath = 'vendor/booosta/ui_datepicker/src';
    if($this->moduleinfo['ui_datepicker'])
      $this->add_includes("
            <link rel='stylesheet' type='text/css' href='{$this->base_dir}$libpath/ui_datepicker.css' />
            ");
  }
}
