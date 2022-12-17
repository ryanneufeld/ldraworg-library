<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Article;

class Navigator extends Component
{
    public $items; // Comma separated list of page aliases to display
    
    public $type; // Default, mobile, or breadcrumbs
    
    public $nodes;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($items = '', $type = '')
    {
        $this->items = str_getcsv($items);
        if ($type == 'mobile' || $type == 'breadcrumbs') {
          $this->type = $type;
        }
        else {
          $this->type = '';
        }
        if (empty($items)) {
          $this->nodes = Article::where('parent_id', '-1')->where('show_in_menu', 1)->where('active', 1)->orderBy('item_order')->get();
        }
        else {
          $this->nodes = Article::whereIn('content_id', $items)->get();
        }
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
      switch ($this->type) {
        case 'mobile':
          return view('components.navigator.index');
        case 'breadcrumbs':
          return view('components.navigator.index');
        default:
          return view('components.navigator.index');
      }    
    }
}
