<?php 
require_once(__DIR__ . "/parsedown/Parsedown.php");
/**
 * Extension of parsedown to handle audio files.
 * code generated with gemini, because i can't be bothered to understand how parsedown works.
 */
class ExtendedParsedown extends Parsedown {
    
    public function __construct() {
        // Register ':' as an inline block lookahead token
        $this->InlineTypes[':'][] = 'Audio';
        $this->inlineMarkerList .= ':';
    }

    protected function inlineAudio($Excerpt) {
        // Regex looks for explicit syntax pattern: :[Label](URL)
        if (preg_match('/^:\[([^\]]+)\]\(([^)]+)\)/', $Excerpt['text'], $matches)) {
            
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'audio',
                    'text' => 'Your browser does not support the audio element.',
                    'attributes' => array(
                        'controls' => 'controls',
                        'src' => $matches[2]
                    )
                )
            );
        }
        
        return null;
    }
}
?>