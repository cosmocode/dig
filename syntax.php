<?php
/**
 * DokuWiki Plugin dig (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_dig extends DokuWiki_Syntax_Plugin {
    function getType() {
        return 'substition';
    }

    function getPType() {
        return 'block';
    }

    function getSort() {
        return 333;
    }


    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<dig>\n.*?\n</dig>',$mode,'plugin_dig');
    }

    function handle($match, $state, $pos, &$handler){

        $lines = explode("\n",$match);
        array_shift($lines); // skip opening tag
        array_pop($lines);   // skip closing tag

        $data = linesToHash($lines);
        return $data;
    }

    function render($mode, &$R, $data) {
        if($mode != 'xhtml') return false;

        $R->doc .= '<table class="inline">';
        $R->doc .= '<th align="right">'.$this->getLang('host').'</th>';
        $R->doc .= '<th>'.$this->getLang('ip').'</th>';
        $R->doc .= '<th align="center">'.$this->getLang('type').'</th>';
        $R->doc .= '<th>'.$this->getLang('ttl').'</th>';
        $R->doc .= '<th>'.$this->getLang('soa').'</th>';
        $R->doc .= '<th>'.$this->getLang('mx').'</th>';
        $R->doc .= '<th>'.$this->getLang('comment').'</th>';

        foreach($data as $domain => $comment){
            $R->doc .= '<tr>';

            $record = dns_get_record($domain,DNS_ALL);

            $ip   = '';
            $type = '';
            $ttl  = '';
            $soa  = '';
            $mx   = '';

            foreach($record as $r){
                if($r['type'] == 'A' || $r['type'] == 'CNAME'){
                    $ip   = $r['ip'];
                    $type = $r['type'];
                    $ttl  = $r['ttl'];
                }elseif($r['type'] == 'SOA'){
                    $soa  = join(' ',array($r['mname'],$r['rname']));
                }elseif($r['type'] == 'MX'){
                    if($mx){
                        $mx .= ', '.$r['target'];
                    }else{
                        $mx = $r['target'];
                    }
                }
            }
            $R->doc .= '<td align="right">'.hsc($domain).'</td>';
            $R->doc .= '<td>'.hsc($ip).'</td>';
            $R->doc .= '<td align="center">'.hsc($type).'</td>';
            $R->doc .= '<td>'.hsc($ttl).'</td>';
            $R->doc .= '<td>'.hsc($soa).'</td>';
            $R->doc .= '<td>'.hsc($mx).'</td>';
            $R->doc .= '<td>'.hsc($comment).'</td>';

            $R->doc .= '</tr>';
        }
        $R->doc .= '</table>';

        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
