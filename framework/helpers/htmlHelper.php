<?php



/**
 * Constructs an html tag.
 *
 * @param  string $name     tag name
 * @param  array  $options  tag options
 * @param  bool   $open     true to leave tag open
 * @return string
 */
function tag($name, $options = array(), $open) {
  if (!$name) {
    return '';
  }

  $tag =  "<{$name}";
  $tag .= generateTagOptions($options);
  if ($open) {
      $tag .= ">";
  }
  else {
      $tag .= " />";
  }
  return $tag;
}

function close_tag($name) {
    return tag("/{$name}",null,true);
}


function generateTagOptions($options) {
    $html = "";
    if (count($options)>0) {
        foreach ($options as $key=>$value) {
            $html .= " {$key}=\"{$value}\" ";
        }
    }
    return $html;
}


/**
 * Returns an <img> image tag for the asset given as argument.
 *
 * <b>Options:</b>
 * - 'absolute' - to output absolute file paths, useful for embedded images in emails
 * - 'alt'  - defaults to the file name part of the asset (capitalized and without the extension)
 * - 'size' - Supplied as "XxY", so "30x45" becomes width="30" and height="45"
 *
 * <b>Examples:</b>
 * <code>
 *  echo image_tag('foobar');
 *    => <img src="images/foobar.png" alt="Foobar" />
 *  echo image_tag('/my_images/image.gif', array('alt' => 'Alternative text', 'size' => '100x200'));
 *    => <img src="/my_images/image.gif" alt="Alternative text" width="100" height="200" />
 * </code>
 *
 * @param string $source  image asset name
 * @param array  $options additional HTML compliant <img> tag parameters
 *
 * @return string XHTML compliant <img> tag
 * @see    image_path
 */
function image_tag($src, $options = array()) {
  if (!$src)  {
    return "";
  }
  $options["src"] = $src;
  $tag = tag("img", $options,false);
  return $tag;
}

function header_tag($number,$content,$options) {
    if (!$number) {
        return "";
    }
    if (intval($number)<0 || intval($number)>6) {
        return "";
    }
    $tag = tag("h{$number}",$options,true);
    $tag .= " {$content} ";
    $tag .= close_tag("h{$number}");

    return $tag;
}

function link_to($url,$title, $options = array()) {
  if (!$url)  {
    return "";
  }
  $options["href"] = $url;
  $options["title"] = $title;
  $tag = tag("a", $options,false);
  $tag .= $title;
  $tag .= close_tag("a");
  return $tag;
}

function link_to_internal($module,$controller,$action,$title, $options = array()) {
  if (!$module || !$controller || !$action)  {
    return "";
  }
  $router = Router::getInstance();
  $url = $router->toURL($module,$controller,$action);
  $options["href"] = $url;
  $options["title"] = $title;
  $tag = tag("a", $options,false);
  $tag .= $title;
  $tag .= close_tag("a");
  return $tag;
}



?>