<?php

return [
  'fileformat' => 'The file :attribute is invalid (:value)',
  'missing' => 'Invalid/missing :attribute line',
  'folder' => 'Selected destination (:folder) is invalid for :attribute line (:value)',
  'line' =>
    [
      'invalid' => 'Line :value invalid',
      'invalidmeta' => 'Line :value, invalid META command or comment without //',
    ],
  'name' =>
    [
      'invalidchars' => 'Only characters a-z, 0-9, _ . and - are allowed in file names',
      'mismatch' => 'Name: line (:value) does not match filename',
      'xparts' => 'Parts with unknown numbers no longer use "x" as a filename prefix. Contact admin to have them assign you a "u" number.',
    ],
  'description' =>
    [
      'missing' => 'First line must be a description line starting "0 "',
      'invalidchars' => 'Description line may not contain special characters',
      'patternword' => 'Pattern part description must end with "Pattern"',
    ],
  'type' =>
    [
      'path' =>  'Path in Name: (:name) is invalid for !LDRAW_ORG part type (:type)',
      'phycolor' => 'Physical Color parts are no longer accepted',
      'alias' => 'Alias parts must have type Part or Shortcut',
      'flex' => 'Flexible Section parts must be of type Part',
      'aliasdesc' => 'Alias part description must begin with "="',
      'flexname' => 'Flexible section file name must end with "kNN"',
      'subpartdesc' => 'Subpart description must begin with "~"',
    ],
  'author' =>
    [
      'registered' => ':value is not a Parts Tracker registerd author',
    ],
  'license' => 
    [
      'approved' => '!LICENSE is not an approved Parts Library license',
      'ccby40' => 'CC BY 4.0 License used but all authors listed have not approved the 4.0 CA'
    ],  
  'category' => 
    [
      'invalid' => ':value is not a valid category',
      'movedto' => 'Moved to part description must begin with "~"',
    ],
  'keywords' => 'Pattern parts and sticker shortcuts must have at least one "Set <setnumber>" keyword',
  'history' =>
    [
      'invalid' => 'Invalid history line(s)',
      'author' => 'History dated :date has an author (:value) who is not registered with the Parts Tracker',
    ],
  'fix' =>
    [
      'unofficial' => 'Official part fixes can only be submitted by an admin, send the part to parts@ldraw.org',
      'checked' => '"New version of official file(s)" must be checked to submit official part updates',
    ],
  'replace' => 'To submit a change to a part already on the Parts Tracker, you must check "Replace existing file(s)"'    
];
