<?php

namespace App\Services\Ai;

use App\Models\User;

/**
 * Busca un capítulo del manual del administrador y lo entrega solo si
 * quien pregunta puede abrir esa pantalla.
 */
class AppManual
{
    /**
     * @return array{ok: bool, sin_acceso?: bool, mensaje?: string, secciones?: list<array{titulo: string, texto: string}>}
     */
    public function search(string $query, ?User $user): array
    {
        $sections = $this->sections();
        $tokens = $this->tokens($query);
        if ($tokens === []) {
            return ['ok' => false, 'mensaje' => 'Indica el tema: por ejemplo resguardo, pedido o kardex.'];
        }

        $ranked = [];
        foreach ($sections as $section) {
            $score = $this->score($tokens, $section['title_folded'], $section['body_folded']);
            if ($score > 0) {
                $ranked[] = ['score' => $score, 'section' => $section];
            }
        }

        if ($ranked === []) {
            return ['ok' => false, 'mensaje' => 'El manual no tiene un procedimiento para ese tema.'];
        }

        usort($ranked, fn ($a, $b) => $b['score'] <=> $a['score']);

        if (! $this->allows($user, $ranked[0]['section'])) {
            $title = $ranked[0]['section']['title'];

            return [
                'ok' => true,
                'sin_acceso' => true,
                'mensaje' => "El usuario no tiene acceso a «{$title}». No describas los pasos. Dile que esa pantalla corresponde a otro perfil.",
            ];
        }

        $allowed = array_values(array_filter(
            $ranked,
            fn ($row) => $this->allows($user, $row['section'])
        ));

        $secciones = [];
        $budget = 7000;
        foreach (array_slice($allowed, 0, 2) as $row) {
            $text = $row['section']['body'];
            if (mb_strlen($text) > 4000) {
                $text = mb_substr($text, 0, 4000).'…';
            }
            if (mb_strlen($text) > $budget) {
                $text = mb_substr($text, 0, $budget).'…';
            }
            $budget -= mb_strlen($text);
            $secciones[] = ['titulo' => $row['section']['title'], 'texto' => $text];
            if ($budget <= 0) {
                break;
            }
        }

        return ['ok' => true, 'sin_acceso' => false, 'secciones' => $secciones];
    }

    /**
     * @return list<array{title: string, title_folded: string, body: string, body_folded: string, permissions: list<string>, roles: list<string>}>
     */
    private function sections(): array
    {
        $path = resource_path('manual/MANUAL_USUARIO.md');
        $markdown = is_file($path) ? (string) file_get_contents($path) : '';
        preg_match_all('/^(#{1,2}) (.+)$/m', $markdown, $matches, PREG_OFFSET_CAPTURE);
        $sections = [];

        foreach ($matches[0] as $index => $match) {
            $start = $match[1] + strlen($match[0]);
            $end = $matches[0][$index + 1][1] ?? strlen($markdown);
            $title = trim($matches[2][$index][0]);
            $body = $this->clean(substr($markdown, $start, $end - $start));
            $access = $this->access($body);
            $sections[] = [
                'title' => $title,
                'title_folded' => $this->fold($title),
                'body' => $body,
                'body_folded' => $this->fold($body),
                'permissions' => $access['permissions'],
                'roles' => $access['roles'],
            ];
        }

        return $sections;
    }

    /** @return array{permissions: list<string>, roles: list<string>} */
    private function access(string $body): array
    {
        if (! preg_match('/\*\*Quién puede:\*\*(.+)/u', $body, $line)) {
            return ['permissions' => [], 'roles' => []];
        }

        preg_match_all('/permiso `([^`]+)`/u', $line[1], $permissions);
        preg_match_all('/rol `([^`]+)`/u', $line[1], $roles);

        return [
            'permissions' => $permissions[1],
            'roles' => $roles[1],
        ];
    }

    /**
     * @param  array{permissions: list<string>, roles: list<string>}  $section
     */
    private function allows(?User $user, array $section): bool
    {
        if ($section['permissions'] === [] && $section['roles'] === []) {
            return true;
        }
        if (! $user) {
            return false;
        }
        foreach ($section['roles'] as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        foreach ($section['permissions'] as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private function clean(string $body): string
    {
        $body = preg_replace('/!\[[^\]]*\]\([^)]*\)\{[^}]*\}/', '', $body) ?? $body;
        $body = preg_replace('/!\[[^\]]*\]\([^)]*\)/', '', $body) ?? $body;
        $body = str_replace([':::', '\\newpage'], '', $body);
        $body = preg_replace("/\n{3,}/", "\n\n", $body) ?? $body;

        return trim($body);
    }

    /** @return list<string> */
    private function tokens(string $query): array
    {
        $folded = $this->fold($query);
        $parts = preg_split('/[^a-z0-9]+/', $folded) ?: [];
        $stop = ['como', 'para', 'que', 'una', 'uno', 'unos', 'unas', 'los', 'las', 'del', 'con', 'por', 'app', 'uso', 'usar', 'hace', 'hacer', 'donde', 'esta', 'esto', 'esta', 'sobre', 'desde', 'cuando', 'cual', 'puede', 'puedo', 'debo'];

        return array_values(array_unique(array_filter(
            $parts,
            fn ($token) => strlen($token) >= 4 && ! in_array($token, $stop, true)
        )));
    }

    /** @param  list<string>  $tokens */
    private function score(array $tokens, string $title, string $body): int
    {
        $score = 0;
        foreach ($tokens as $token) {
            if (str_contains($title, $token)) {
                $score += 5;
            } elseif (str_contains($body, $token)) {
                $score += 1;
            }
        }

        return $score;
    }

    private function fold(string $text): string
    {
        $text = mb_strtolower($text);

        return strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
    }
}
