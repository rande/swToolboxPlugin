<?php


class swCompilerStats {
  
  static $instance;
  protected 
    $stats,
    $file,
    $base,
    $to_include,
    $level = 0,
    $_stats = array()
  ;
  
  private function __construct($file, $base, $to_include)
  {
    $this->file = $file;
    $this->base = $base;
    $this->to_include = is_array($to_include) ? $to_include : array();
    
    $this->load();
  }
  
  public static function getInstance()
  {
    if(is_null(self::$instance))
    {
      
      if(count(func_get_args()) != 3) 
      {
        throw new RuntimeException('argument number is wrong');
      }
      
      $args = func_get_args();
      
      self::$instance = new swCompilerStats($args[0], $args[1], $args[2]);
    }
    
    return self::$instance;
  }
  
  public function load()
  {
    if(is_readable($this->file))
    {
      $this->stats = unserialize(file_get_contents($this->file));
    }

    if(!$this->stats)
    {
      $this->stats = array();
    }
    
  }
  
  public function start()
  {
    $this->level++;
  
  }
  
  public function end()
  {
    $this->level--;
    
    if($this->level == 0)
    {
      for( $i = count($this->_stats); $i > 0; $i--)
      {

        $stats = $this->_stats[$i];
        $class = $this->_stats[$i]['class'];

        if(!array_key_exists($class, $this->stats))
        {
          $this->stats[$class] = $stats;
        }
        
        $this->stats[$class]['count']++;
        
      }
      
      $this->_stats = array();
    }
    
  }
  
  public function requireFile($class, $path)
  {
    $this->start();
    
    require($path);
    
    $this->add($class, $path);
    $this->end();
    
  }
  
  public function add($class, $path)
  {
    $cut = strlen($this->base);

    $path = substr($path, $cut);
    
    $this->_stats[$this->level] = array(
      'path' => $path,
      'level' => $this->level,
      'count' => 0,
      'class' => $class,
    );
    
  }

  public function dump($file_core_compile, $limit)
  {
    $dump = '';
    
    foreach($this->to_include as $class => $path)
    {
      $dump .= $path."\n";
    }
    
    foreach($this->stats as $class => $info)
    {
      if($info['count'] < $limit)
      {
        //continue;
      }

      $dump .= '- %SF_ROOT_DIR%'.$info['path']." # ".$info['count']."\n";
    }

    file_put_contents($file_core_compile, $dump);
  }
  
  public function save()
  {
    
    file_put_contents($this->file, serialize($this->stats));
  }
}