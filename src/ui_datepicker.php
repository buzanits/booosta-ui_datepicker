<?php
namespace booosta\ui_datepicker;

use \booosta\Framework as b;
b::init_module('ui_datepicker');

class UI_Datepicker extends \booosta\calendar\Calendar
{
  use moduletrait_ui_datepicker;

  protected $dayClickCode;
  protected $eventBackgroundColor;
  protected $id_prefix = 'ui_datepicker';


  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['ui_datepicker'] = true;
      if($this->topobj->moduleinfo['jquery']['use'] == '') $this->topobj->moduleinfo['jquery']['use'] = true;
      if($this->topobj->moduleinfo['jquery']['use_ui'] == '') $this->topobj->moduleinfo['jquery']['use_ui'] = true;
    endif;
  }

  public function set_enddate($date) { $this->date = date('Y-m-d', strtotime($date)); }
  public function set_dayClickCode($code) { $this->dayClickCode = $code; }
  public function set_eventBackgroundColor($code) { $this->eventBackgroundColor = $code; }

  public function get_htmlonly() { 
    return "<div id='$this->id'></div>";
  }

  public function get_js()
  {
    $eventlist = '';
    ksort($this->events);
    foreach($this->events as $event):
      $d = $event->get_data();
      #\booosta\debug($d);

      if($d['enddate']) $enddate = "end: '{$d['enddate']}'";
      else $enddate = "end: '" . date('Y-m-d H:i:s', strtotime($d['date'] . ' +1 hour')) . "'";

      if($d['link']) $url = "url: '{$d['link']}', "; else $url = '';

      $eventlist .= "{ id: {$d['id']}, title: '{$d['name']}', start: '{$d['date']}', $url $enddate }, ";
    endforeach;

    if($this->dayClickCode)
      $dayClickCode = "var act_view = view.name; var clicked_date = date.format(); $this->dayClickCode";

    if($this->eventBackgroundColor) $bgcolor = "eventBackgroundColor: '$this->eventBackgroundColor', eventBorderColor: '$this->eventBackgroundColor', ";
    if($this->date) $datestr = "defaultDate: '$this->date', defaultView: 'agendaDay', ";

    $code = "$('#$this->id').datepicker({
        dateFormat: 'yy-mm-dd', defaultDate: '$this->date', onSelect: function(date, inst) { $this->dayClickCode }, });
        $('#$this->id').datepicker('option', $.datepicker.regional['$this->lang']);";

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\webapp")):
      $this->topobj->add_jquery_ready($code);
      return '';
    else:
      return "\$(document).ready(function(){ $code });";
    endif;
  }
}
