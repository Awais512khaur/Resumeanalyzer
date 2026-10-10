<?php

if (!function_exists('get_position_required_skills')) {
    /**
     * Read explicitly entered skills, or recover short structured skill entries
     * from older positions whose skills were entered as individual HTML blocks.
     */
    function get_position_required_skills(array $position): array
    {
        $storedSkills = trim((string)($position['required_skills'] ?? ''));
        if ($storedSkills !== '') {
            $skills = preg_split('/[,;|\r\n]+/', $storedSkills);
        } else {
            $description = (string)($position['description'] ?? '');
            $blocks = [];
            if (preg_match_all('/<(li|p)\b[^>]*>(.*?)<\/\1>/is', $description, $matches)) {
                $preferredTag = preg_match('/<li\b/i', $description) ? 'li' : 'p';
                foreach ($matches[1] as $index => $tag) {
                    if (strtolower($tag) !== $preferredTag) {
                        continue;
                    }
                    $text = html_entity_decode(strip_tags($matches[2][$index]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $text = trim(preg_replace('/\s+/u', ' ', $text));
                    if ($text !== '') {
                        $blocks[] = $text;
                    }
                }
            }

            // Only consider structured short blocks skills; don't turn prose into a skill list.
            $looksLikeSkillList = count($blocks) > 1;
            foreach ($blocks as $block) {
                if (mb_strlen($block) > 64 || preg_match('/[.!?]/u', $block)) {
                    $looksLikeSkillList = false;
                    break;
                }
            }
            $skills = $looksLikeSkillList ? $blocks : [];
        }

        $unique = [];
        $seen = [];
        foreach ($skills as $skill) {
            $skill = trim(html_entity_decode((string)$skill, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $key = strtolower($skill);
            if ($skill !== '' && !isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $skill;
            }
        }
        return $unique;
    }
}

if (!function_exists('position_description_plain_text')) {
    function position_description_plain_text(string $description): string
    {
        $description = preg_replace('/<br\s*\/?\s*>/i', "\n", $description);
        $description = preg_replace('/<\/(?:p|li|div|h[1-6])\s*>/i', "\n", $description);
        $description = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_map('trim', preg_split('/\r\n|\r|\n/', $description));
        return implode("\n", array_filter($lines, static fn($line) => $line !== ''));
    }
}
