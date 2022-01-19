<?php
namespace booosta\ui_datepicker;

use \booosta\Framework as b;
b::init_module('ui_datepicker');

class UI_Datepicker extends \booosta\ui\UI
{
  use moduletrait_ui_datepicker;

  protected $events;
  protected $date;
  protected $dayClickCode;
  protected $eventBackgroundColor;

  public function __construct($name = null, $events = null, $events_url = null)
  {
    parent::__construct();
    if($events === null) $this->events = []; else $this->events = $events;
    $this->id = "ui_datepicker_$name";
    $this->lang = $this->config('language');
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['ui_datepicker'] = true;
      if($this->topobj->moduleinfo['jquery']['use'] == '') $this->topobj->moduleinfo['jquery']['use'] = true;
      if($this->topobj->moduleinfo['jquery']['use_ui'] == '') $this->topobj->moduleinfo['jquery']['use_ui'] = true;
    endif;
  }

  public function add_events($events) { if(is_array($events)) $this->events = array_merge($this->events, $events); }
  public function set_date($date) { $this->date = date('Y-m-d', strtotime($date)); }
  public function set_enddate($date) { $this->date = date('Y-m-d', strtotime($date)); }
  public function set_lang($lang) { $this->lang = $lang; }
  public function get_lang() { return $this->lang; }
  public function set_dayClickCode($code) { $this->dayClickCode = $code; }
  public function set_eventBackgroundColor($code) { $this->eventBackgroundColor = $code; }

  public function add_event($event)
  {
    if(is_object($event)): $this->events[$event->sortkey()] = $event;
    elseif(is_array($event)):
      #\booosta\debug($event);
      $obj = $this->makeInstance("\\booosta\\ui_datepicker\\Event", $event['name'], $event['date']);
      if($event['id']) $obj->set_id($event['id']);
      if($event['enddate']) $obj->set_enddate($event['enddate']);
      if($event['link']) $obj->set_link($event['link']);
      if($event['link_target']) $obj->set_link_target($event['link_target']);
      if($event['description']) $obj->set_description($event['description']);
      if(is_array($event['settings'])) $obj->set_event_settings($event['settings']);;

      $this->events[$obj->sortkey()] = $obj;
    endif;
  }

  public function set_events_url($url) { $this->events_url = $url; }

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

class Event extends \booosta\Base\base
{
  protected $id, $name, $date, $enddate, $link, $link_target, $description, $settings;

  public function __construct($name, $date, $link = null, $link_target = null, $description = null)
  {
    parent::__construct();
    $this->name = $name;
    $this->date = $date;
    $this->link = $link;
    $this->link_target = $link_target;
    $this->description = $description;
    $this->settings = [];
  }

  public function get_id() { return $this->id; }
  public function get_name() { return $this->name; }
  public function get_date() { return $this->date; }
  public function get_enddate() { return $this->enddate; }
  public function get_link() { return $this->link; }
  public function get_link_target() { return $this->link_target; }
  public function get_description() { return $this->description; }
  public function get_event_settings() { return $this->settings; }
  public function set_name($val) { $this->name = $val; }
  public function set_id($val) { $this->id = $val; }
  public function set_date($val) { $this->date = $val; }
  public function set_enddate($val) { $this->enddate = $val; }
  public function set_link($val) { $this->link = $val; }
  public function set_link_target($val) { $this->link_target = $val; }
  public function set_description($val) { $this->description = $val; }
  public function set_settings($val) { $this->settings = $val; }

  public function get_event_setting($key) { return $this->settings[$key]; }
  public function set_event_setting($key, $val) { $this->settings[$key] = $val; }

  public function sortkey() { return date('YmdHis', strtotime($this->date)) . uniqid(); }

  public function get_data() 
  { 
    $data = get_object_vars($this);
    unset($data['parentobj']);
    unset($data['topobj']);
    unset($data['CONFIG']);

    return $data;
  }

  public function is_at_day($date)
  { 
    $date = date('Y-m-d', strtotime($date));
    return $this->is_between("$date 00:00:00", "$date 23:59:59");
  }

  public function is_between($from, $until)
  { 
    $from = strtotime($from);
    $until = strtotime($until);
    return strtotime($this->date) >= $from && strtotime($this->date) <= $until;
  }

  public function is_before($date)
  { 
    $date = strtotime($date);
    return strtotime($this->date) < $date;
  }

  public function is_after($date)
  {
    $date = strtotime($date);
    return strtotime($this->date) > $date;
  }
}
