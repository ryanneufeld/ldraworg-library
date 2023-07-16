<?php

namespace App\LDraw;

use App\Models\User;

class FileUtils
{
  /**
   * fixEncoding - Ensure Correct UTF-8 encoding
   *
   * There are/were several badly encoded UTF-8 files in the library.
   * Hopefully this prevents this from happening in the future
   * 
   * @param  string $text
   * 
   * @return string
   */
  public static function fixEncoding(string $text): string
  {
    return mb_convert_encoding($text, 'UTF-8', ['ASCII', 'ISO-8859-1', 'UTF-8']);
  }

    
  /**
   * unix2dos - Change to DOS style line endings
   *
   * @param  string $text
   * 
   * @return string
   */
  public static function unix2dos(string $text): string
  {
    return preg_replace('#\R#us', "\r\n", $text);
  }

    
  /**
   * dos2unix - Change to UNIX line endings
   *
   * @param  string $text
   * 
   * @return string
   */
  public static function dos2unix(string $text): string
  {
    return preg_replace('#\R#us', "\n", $text);
  }
  
  /**
   * formatText - Uniformly format test
   *
   * Changes to UNIX style line endings, strips extra spaces and newlines,
   * and lower cases a type 1 line file references
   * 
   * @param  string $text
   * 
   * @return string
   */
  public static function formatText(string $text): string
  {
    $text = self::fixEncoding($text);
    $text = self::dos2unix($text);
    $text = preg_replace('#\n{3,}#us', "\n\n", $text);
    $text = explode("\n", $text);
    foreach ($text as $index => &$line) {
      if ($index === array_key_first($text)) {
        continue;
      }
      $line = preg_replace('#\h+#u', ' ', trim($line));
      if (! empty($line) && $line[0] === '1') {
        $line = mb_strtolower($line);
      }
    }
    $text = implode("\n", $text);

    return $text;
  }
  
  /**
   * cleanFileText - Formats file for library parsing
   *
   * @param string $text
   * @param bool $forceunofficial
   * @param bool $author_from_db
   * 
   * @return string
   */
  public static function cleanFileText(string $text, bool $forceunofficial = false, bool $author_from_db = false): string
  {
    $text = self::cleanHeader(self::formatText($text), $forceunofficial, $author_from_db);

    return $text;
  }

  public static function headerEndLine($file)
  {
    $file = preg_split("#\R#u", $file);
    if (empty($file)) {
      return 0;
    }
    $i = 1;
    while ($i < count($file)) {
      if (empty($file[$i]) ||
         ($file[$i][0] === '0' &&
          in_array(strtok(mb_substr($file[$i], 1), ' '), config('ldraw.allowed_metas.header'), true) &&
          $file[$i] !== '0 BFC NOCLIP' && $file[$i] !== '0 BFC INVERTNEXT' && $file[$i] !== '0 BFC CLIP')
      ) {
        $i++;
      } else {
        break;
      }
    }

    return $i - 1;
  }

  /**
   * getHeader - Get the file header
   *
   * @param string $file
   * 
   * @return string
   */
  public static function getHeader(string $file): string
  {
    $filearr = preg_split("#\R#u", $file);
    return empty($filearr) ? '' : implode("\n", array_slice($filearr, 0, self::headerEndLine($file) + 1));
  }

  /**
   * setHeader - Set the file header
   *
   * @param string $file
   * @param string $header
   * 
   * @return string
   */
  public static function setHeader(string $file, string $header): string
  {
    $filearr = preg_split("#\R#u", $file);

    return "$header\n" . implode("\n", array_slice($filearr, self::headerEndLine($file) + 1));
  }

  /**
   * cleanHeader - Reformat the header
   *
   * This function does no validation and can produce an invalid header
   * 
   * @param string $file
   * @param bool $forceunofficial
   * @param bool $author_from_db
   * 
   * @return string
   */
  public static function cleanHeader(string $file, bool $forceunofficial = false, bool $author_from_db = false): string
  {
    $header = '0 '. self::getDescription($file)."\n";
    $header .= '0 Name: '. mb_strtolower(self::getName($file)) . "\n";

    $aline = '';
    $author = self::getAuthor($file);
    if (! empty($author)) {
      if ($author_from_db) {
        $user = User::findByName($author['user'], $author['realname']);
        if (! empty($user)) {
          $aline = $user->authorString();
        }
      } else {
        if (! empty($author['realname'])) {
          $aline = $author['realname'];
        }
        if (! empty($author['user'])) {
          $aline .= ' [' . $author['user'] . ']';
        }
        $aline = trim($aline);
      }
    }
    $header .= "0 Author: $aline\n";

    $parttype = self::getPartType($file);
    $release = self::getRelease($file);
    if (! empty($parttype)) {
      if ($forceunofficial) {
        $parttype['unofficial'] = 'Unofficial_';
      }
      $ptline = $parttype['unofficial'].$parttype['type'];
      if (! empty($parttype['qual'])) {
        $ptline .= ' ' . $parttype['qual'];
      }
    } else {
      $ptline = '';
    }
    if (! empty($ptline) && ! empty($release) && ! $forceunofficial) {
      $ptline .= ' ' . $release['releasetype'];
      if ($release['releasetype'] == 'UPDATE') {
        $ptline .= ' ' . $release['release'];
      }
    }
    $header .= "0 !LDRAW_ORG $ptline\n";

    $header .= '0 !LICENSE ' . self::getLicense($file) . "\n\n";

    $help = self::getHelp($file);
    if (! empty($help)) {
      foreach ($help as $hline) {
        $header .= "0 !HELP $hline\n";
      }
      $header .= "\n";
    }

    $bfc = self::getBFC($file);
    if (! empty($bfc) && ! empty($bfc['certwinding'])) {
      $header .= '0 BFC CERTIFY '.$bfc['certwinding']."\n\n";
    } else {
      $header .= "0 BFC NOCERTIFY\n\n";
    }

    $category = self::getCategory($file);
    if (! empty($category) && $category['meta'] === true) {
      $header .= '0 !CATEGORY '.$category['category']."\n";
    }

    $keywords = self::getKeywords($file);
    if (! empty($keywords)) {
      $kwline = '0 !KEYWORDS ';
      foreach ($keywords as $index => $kw) {
        if (mb_strlen($kwline.', '.$kw) > 80) {
          $header .= "$kwline\n";
          $kwline = "0 !KEYWORDS $kw";
        } else {
          if ($index !== array_key_first($keywords)) {
            $kwline .= ', ';
          }
          $kwline .= $kw;
        }
      }
      $header .= "$kwline\n\n";
    } elseif (! empty($category) && $category['meta']) {
      $header .= "\n";
    }

    $cmdline = self::getCmdLine($file);
    if (! empty($cmdline)) {
      $header .= "0 !CMDLINE $cmdline\n\n";
    }

    $history = self::getHistory($file);
    if (! empty($history)) {
      usort($history, function ($a, $b) {
        return strtotime($a['date']) <=> strtotime($b['date']);
      });
      foreach ($history as $hist) {
        $histline = '0 !HISTORY '.$hist['date'].' ';
        $user = User::findByName($hist['user'], $hist['user']);
        if (! is_null($user)) {
          $histline .= $user->historyString().' ';
        } else {
          $histline .= '['.$hist['user'].'] ';
        }
        $header .= $histline.$hist['comment']."\n";
      }
    }

    return self::setHeader($file, $header);
  }

  /**
   * getDescription - Get the file description
   *
   * @param string $file
   * 
   * @return string|bool
   */
  public static function getDescription(string $file): string|bool
  {
    if (preg_match(config('ldraw.patterns.description'), $file, $matches)) {
      return empty(trim($matches['description'])) ? false : trim($matches['description']);
    } else {
      return false;
    }
  }

  /**
   * getName - Get the Name: meta value
   *
   * @param string $file
   * 
   * @return string|bool
   */
  public static function getName(string $file): string|bool
  {
    if (preg_match(config('ldraw.patterns.name'), $file, $matches)) {
      return empty(trim($matches['name'])) ? false : trim($matches['name']);
    } else {
      return false;
    }
  }

  public static function getLicense(string $file): string|bool
  {
    if (preg_match(config('ldraw.patterns.license'), $file, $matches)) {
      return empty(trim($matches['license'])) ? false : trim($matches['license']);
    } else {
      return false;
    }
  }

  public static function getCmdLine(string $file): string|bool
  {
    if (preg_match(config('ldraw.patterns.cmdline'), $file, $matches)) {
      return empty(trim($matches['cmdline'])) ? false : trim($matches['cmdline']);
    } else {
      return false;
    }
  }

  public static function getAuthor(string $file): array|bool
  {
    if (preg_match(config('ldraw.patterns.author'), $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['user2' => '', 'realname' => '', 'user' => ''], $matches);
      if (empty(trim($matches['user2'])) && empty(trim($matches['realname'])) && empty(trim($matches['user']))) {
        return false;
      }
      if (empty($matches['realname'])) {
        $matches['user'] = $matches['user2'];
      }

      return ['realname' => $matches['realname'], 'user' => $matches['user']];
    } else {
      return false;
    }
  }

  /**
   * getPartType
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getPartType(string $file): array|bool
  {
    $pattern = str_replace('###PartTypes###', implode('|', \App\Models\PartType::pluck('type')->all()), config('ldraw.patterns.type'));
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', \App\Models\PartTypeQualifier::pluck('type')->all()), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['unofficial' => '', 'type' => '', 'qual' => ''], $matches);

      return ['unofficial' => $matches['unofficial'], 'type' => $matches['type'], 'qual' => $matches['qual']];
    } else {
      return false;
    }
  }

  /**
   * getRelease
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getRelease(string $file): array|bool
  {
    $pattern = str_replace('###PartTypes###', implode('|', \App\Models\PartType::pluck('type')->all()), config('ldraw.patterns.type'));
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', \App\Models\PartTypeQualifier::pluck('type')->all()), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      $matches = array_merge(['releasetype' => '', 'release' => ''], $matches);
      if ($matches['releasetype'] == 'ORIGINAL') {
      $matches['release'] = 'original';
      }

      return ['releasetype' => $matches['releasetype'], 'release' => $matches['release']];
    } else {
      return false;
    }
  }

  // Only returns the first valid BFC statement
  /**
   * getBFC
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getBFC(string $file): array|bool
  {
    if (preg_match(config('ldraw.patterns.bfc'), $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['bfc' => '', 'certwinding' => '', 'clipwinding' => ''], $matches);

      return ['bfc' => preg_replace('#\s+#u', ' ', $matches['bfc']), 'certwinding' => $matches['certwinding'], 'clipwinding' => $matches['clipwinding']];
    } else {
      return false;
    }
  }

  /**
   * getCategory
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getCategory(string $file): array|bool
  {
    $d = self::getDescription($file);
    if (preg_match(config('ldraw.patterns.category'), $file, $matches)) {
      $c = trim($matches['category']);
      empty($c) ? $cat = false : $cat = ['category' => $c, 'meta' => true];
    } elseif ($d !== false && mb_strpos($file, '0 !CATEGORY') === false) {
      $c = trim(str_replace(['~', '|', '=', '_'], '', explode(' ', trim($d))[0]));
      empty($c) ? $cat = false : $cat = ['category' => $c, 'meta' => false];
    } else {
      $cat = false;
    }

    return $cat;
  }

  /**
   * getHistory
   *
   * @param string $file
   * @param bool $get_user_ids
   * 
   * @return array|bool
   */
  public static function getHistory(string $file, bool $get_user_ids = false): array|bool
  {
    if (preg_match_all(config('ldraw.patterns.history'), $file, $matches, PREG_SET_ORDER) > 0) {
      $history = [];
      $aliases = config('ldraw.known_author_aliases');
      foreach ($matches as $match) {
        if ($get_user_ids) {
          if (array_key_exists($match['user'], $aliases)) {
          $match['user'] = $aliases[$match['user']];
          }
          $user = User::findByName($match['user'], $match['user']);
          if (! empty($user)) {
            $uid = $user->id;
          } else {
            $uid = -1;
          }
        } else {
          $uid = $match['user'];
        }
        $history[] = ['date' => $match['date'], 'user' => $uid, 'comment' => $match['comment']];
      }

      return $history;
    } else {
      return false;
    }
  }

  /**
   * getHelp
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getHelp(string $file): array|bool
  {
    if (preg_match_all(config('ldraw.patterns.help'), $file, $matches) > 0) {
      return array_values(array_filter($matches['help']));
    } else {
      return false;
    }
  }

  /**
   * getKeywords
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getKeywords(string $file): array|bool
  {
    if (preg_match_all(config('ldraw.patterns.keywords'), $file, $matches) > 0) {
      $keywords = [];
      foreach ($matches['keywords'] as $line) {
        $line = explode(',', $line);
        foreach ($line as $word) {
          $word = trim($word);
          $word = preg_replace('#\h+#u', ' ', $word);
          $word = preg_replace('#^[\'"](.*)[\'"]$#u', '$1', $word);
          if (! empty($word)) {
          $keywords[] = $word;
          }
        }
      }
      $keywords = array_unique($keywords);

      return empty($keywords) ? false : $keywords;
    } else {
      return false;
    }
  }

  /**
   * getSubparts
   *
   * @param string $file
   * 
   * @return array|bool
   */
  public static function getSubparts(string $file): array|bool
  {
    $result = ['subparts' => [], 'textures' => []];
    if (preg_match_all(config('ldraw.patterns.subparts'), $file, $matches) > 0) {
      array_walk($matches['subpart'], function (&$arg) {
        $arg = mb_strtolower($arg);
      });
      $result['subparts'] = array_values(array_filter(array_unique($matches['subpart'])));
    }
    if (preg_match_all(config('ldraw.patterns.textures'), $file, $matches) > 0) {
      $result['textures'] = $matches['texture1'];
      if (isset($matches['texture2'])) {
      $result['textures'] = array_merge($result['textures'], $matches['texture2']);
      }
      array_walk($result['textures'], function (&$arg) {
        $arg = mb_strtolower($arg);
      });
      $result['textures'] = array_values(array_filter(array_unique($result['textures'])));
    }
    if (! empty($result['subparts']) || ! empty($result['textures'])) {
      return $result;
    } else {
      return false;
    }
  }
}
