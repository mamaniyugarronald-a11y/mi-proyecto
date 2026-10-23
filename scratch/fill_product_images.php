<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$directory = public_path('img/productos');
if (!is_dir($directory)) mkdir($directory, 0775, true);

$items = DB::table('producto as p')
    ->join('tipo_producto as t', 't.id_tipo_producto', '=', 'p.id_tipo_producto')
    ->select('p.id_producto', 'p.nombre', 'p.imagen', 't.nombre_tipo as categoria')
    ->whereNull('p.imagen')
    ->orderBy('p.id_producto')
    ->get();

$palette = [
    ['#e0f2fe', '#0284c7', '#075985'], ['#fce7f3', '#db2777', '#9d174d'],
    ['#dcfce7', '#16a34a', '#166534'], ['#fef3c7', '#d97706', '#92400e'],
    ['#ede9fe', '#7c3aed', '#5b21b6'], ['#ffedd5', '#ea580c', '#9a3412'],
];

function productKind(string $name, string $category): string {
    $n = mb_strtolower($name);
    if (str_contains($n, 'vajilla')) return 'dinnerware';
    if (str_contains($n, 'plato')) return 'plate';
    if (str_contains($n, 'bowl') || str_contains($n, 'tason') || str_contains($n, 'tazón')) return 'bowl';
    if (str_contains($n, 'vaso') || str_contains($n, 'tasa') || str_contains($n, 'taza')) return 'glass';
    if (str_contains($n, 'jarra') || str_contains($n, 'termo')) return 'jug';
    if (str_contains($n, 'procesador') || str_contains($n, 'licuadora')) return 'blender';
    if (str_contains($n, 'freidora')) return 'fryer';
    if (str_contains($n, 'plancha')) return 'iron';
    if (str_contains($n, 'olla a presion')) return 'pressure';
    if (str_contains($n, 'olla') || str_contains($n, 'jgo ollas')) return 'pot';
    if (str_contains($n, 'hervidor')) return 'kettle';
    if (str_contains($n, 'cafetera')) return 'coffee';
    if (str_contains($n, 'batidora')) return 'mixer';
    if (str_contains($n, 'secadora')) return 'dryer';
    if (str_contains($n, 'donera') || str_contains($n, 'huevero')) return 'egg';
    if (str_contains($n, 'dispensador')) return 'dispenser';
    if (str_contains($n, 'exprimidor')) return 'juicer';
    if (str_contains($n, 'cubierto') || str_contains($n, 'cuchillo')) return 'cutlery';
    if (str_contains($n, 'organizador')) return 'organizer';
    if (str_contains($n, 'contenedor')) return 'containers';
    if (str_contains($n, 'lámpara') || str_contains($n, 'lampara')) return 'lamp';
    if (str_contains($n, 'pairex') || str_contains($n, 'alcusa')) return 'glassware';
    if (str_contains(mb_strtolower($category), 'vidrio')) return 'glassware';
    return 'home';
}

function productShape(string $kind, string $accent): string {
    $stroke = '#334155';
    return match ($kind) {
        'dinnerware' => '<ellipse cx="120" cy="113" rx="69" ry="51" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><ellipse cx="120" cy="113" rx="48" ry="34" fill="#f8fafc" stroke="'.$accent.'" stroke-width="4"/><path d="M95 113h50M120 88v50" stroke="'.$accent.'" stroke-width="3"/>',
        'plate' => '<circle cx="120" cy="112" r="61" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><circle cx="120" cy="112" r="43" fill="#f8fafc" stroke="'.$accent.'" stroke-width="4"/><circle cx="120" cy="112" r="5" fill="'.$accent.'"/>',
        'bowl' => '<path d="M53 91h134c-5 49-28 72-67 72S58 140 53 91Z" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><path d="M64 101h112" stroke="'.$accent.'" stroke-width="8" stroke-linecap="round"/><path d="M79 163h82" stroke="'.$stroke.'" stroke-width="5" stroke-linecap="round"/>',
        'glass', 'glassware' => '<path d="M75 65h90l-9 67c-3 23-15 33-36 33s-33-10-36-33l-9-67Z" fill="#e0f2fe" fill-opacity=".8" stroke="'.$stroke.'" stroke-width="5"/><path d="M83 104h74M120 165v22m-28 0h56" stroke="'.$accent.'" stroke-width="5" stroke-linecap="round"/>',
        'jug' => '<path d="M82 68h65v17c20 7 28 22 28 44v31c0 12-9 21-21 21H87c-12 0-21-9-21-21v-49c0-18 7-31 16-39Z" fill="#e0f2fe" stroke="'.$stroke.'" stroke-width="5"/><path d="M147 98h17c24 0 25 42 0 42h-9M94 68V56h40v12" fill="none" stroke="'.$accent.'" stroke-width="6" stroke-linecap="round"/>',
        'blender' => '<path d="M89 61h62l-7 89H96l-7-89Z" fill="#e0f2fe" stroke="'.$stroke.'" stroke-width="5"/><path d="M98 74h44v53H98z" fill="#fff" stroke="'.$accent.'" stroke-width="4"/><path d="M101 150h38l12 18H89l12-18Zm-8 18h56v18H93z" fill="'.$accent.'" stroke="'.$stroke.'" stroke-width="4"/>',
        'fryer' => '<rect x="66" y="72" width="108" height="102" rx="19" fill="#1e293b" stroke="'.$stroke.'" stroke-width="5"/><rect x="80" y="91" width="80" height="53" rx="9" fill="#334155"/><path d="M94 108h52m-52 14h33" stroke="#f8fafc" stroke-width="5" stroke-linecap="round"/><circle cx="147" cy="159" r="6" fill="'.$accent.'"/>',
        'iron' => '<path d="M57 141c4-34 22-52 53-58l48 6 19 52H57Z" fill="#e0f2fe" stroke="'.$stroke.'" stroke-width="5"/><path d="M110 83l7-27h35l15 36" fill="none" stroke="'.$accent.'" stroke-width="9" stroke-linecap="round"/><path d="M67 142h107" stroke="'.$accent.'" stroke-width="7"/>',
        'pressure', 'pot' => '<path d="M66 91h108v66c0 14-12 25-27 25H93c-15 0-27-11-27-25V91Z" fill="#cbd5e1" stroke="'.$stroke.'" stroke-width="5"/><path d="M57 91h126V78H57zM91 69h58m-29 0V56m-47 35V79m94 12V79" fill="none" stroke="'.$accent.'" stroke-width="7" stroke-linecap="round"/><path d="M66 117H48m126 0h18" stroke="'.$stroke.'" stroke-width="8"/>',
        'kettle' => '<path d="M75 94c0-22 16-37 43-37s43 15 43 37v72H75V94Z" fill="#e2e8f0" stroke="'.$stroke.'" stroke-width="5"/><path d="M75 108H58c-20 0-20 34 0 34h17m86-50 17-14m-72-21V47" fill="none" stroke="'.$accent.'" stroke-width="7" stroke-linecap="round"/><path d="M88 168v10h60v-10" fill="'.$accent.'"/>',
        'coffee' => '<rect x="77" y="80" width="86" height="93" rx="10" fill="#e2e8f0" stroke="'.$stroke.'" stroke-width="5"/><path d="M88 91h64v33H88z" fill="#fff" stroke="'.$accent.'" stroke-width="4"/><path d="M100 139v10m20-10v10m20-10v10" stroke="'.$accent.'" stroke-width="5"/><path d="M88 173h64v14H88z" fill="#475569"/>',
        'mixer' => '<path d="M72 68h95v35c0 30-20 49-47 49s-48-19-48-49V68Z" fill="#e2e8f0" stroke="'.$stroke.'" stroke-width="5"/><path d="M88 68V56h63v12m-49 84-13 31m44-31 13 31" stroke="'.$accent.'" stroke-width="7" stroke-linecap="round"/>',
        'dryer' => '<rect x="75" y="68" width="90" height="122" rx="25" fill="#e0f2fe" stroke="'.$stroke.'" stroke-width="5"/><path d="M95 91h50m-50 16h50m-50 16h50" stroke="'.$accent.'" stroke-width="6" stroke-linecap="round"/><path d="M120 68V53" stroke="'.$stroke.'" stroke-width="8"/>',
        'egg' => '<path d="M120 55c-28 0-48 40-48 75 0 31 17 51 48 51s48-20 48-51c0-35-20-75-48-75Z" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><path d="M89 128h62m-56 17h50" stroke="'.$accent.'" stroke-width="6"/><circle cx="120" cy="95" r="8" fill="'.$accent.'"/>',
        'dispenser' => '<path d="M86 68h68v18H86zM94 86h52v84H94z" fill="#e0f2fe" stroke="'.$stroke.'" stroke-width="5"/><path d="M120 55v13m-10 40h20m-10 0v24m-11 0h22" stroke="'.$accent.'" stroke-width="6" stroke-linecap="round"/><path d="M106 170v15h28v-15" fill="#475569"/>',
        'juicer' => '<path d="M120 62c-14 22-31 37-31 61a31 31 0 0 0 62 0c0-24-17-39-31-61Z" fill="#fbbf24" stroke="'.$stroke.'" stroke-width="5"/><path d="M75 127h90l-10 47H85l-10-47Z" fill="#e2e8f0" stroke="'.$stroke.'" stroke-width="5"/><path d="M96 126c2-20 12-29 24-29s22 9 24 29" fill="none" stroke="'.$accent.'" stroke-width="6"/>',
        'cutlery' => '<path d="M75 60v55m-13-55v36c0 13 5 19 13 19s13-6 13-19V60m-13 55v70m57-125c-15 20-18 44-18 69h18V60Zm0 69v56" fill="none" stroke="'.$stroke.'" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>',
        'organizer', 'containers' => '<rect x="65" y="62" width="110" height="40" rx="8" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><rect x="65" y="108" width="110" height="40" rx="8" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><rect x="65" y="154" width="110" height="34" rx="8" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><path d="M75 73h90m-90 46h90m-90 44h90" stroke="'.$accent.'" stroke-width="6"/>',
        'lamp' => '<path d="M80 83c0-25 18-42 40-42s40 17 40 42l-13 41H93L80 83Z" fill="#fef3c7" stroke="'.$stroke.'" stroke-width="5"/><path d="M94 124h52v18H94zm12 18v34m28-34v34m-39 0h50" fill="none" stroke="'.$accent.'" stroke-width="7" stroke-linecap="round"/>',
        default => '<path d="M65 91h110v76H65z" fill="#fff" stroke="'.$stroke.'" stroke-width="5"/><path d="M82 110h76m-76 18h56m-56 18h66" stroke="'.$accent.'" stroke-width="6" stroke-linecap="round"/>',
    };
}

$saved = 0;
DB::transaction(function () use ($items, $directory, $palette, &$saved) {
    foreach ($items as $item) {
        $filename = 'producto_' . (int) $item->id_producto . '.svg';
        $colors = $palette[((int) $item->id_producto) % count($palette)];
        $title = htmlspecialchars($item->nombre, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $kind = productKind($item->nombre, $item->categoria);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" role="img" aria-label="' . $title . '">'
            . '<rect width="240" height="240" rx="24" fill="#f8fafc"/><circle cx="120" cy="112" r="91" fill="' . $colors[0] . '"/>'
            . productShape($kind, $colors[1])
            . '<text x="120" y="222" text-anchor="middle" font-family="Arial,sans-serif" font-size="11" font-weight="700" fill="' . $colors[2] . '">'
            . htmlspecialchars(mb_strtoupper(mb_substr($item->nombre, 0, 27)), ENT_QUOTES | ENT_XML1, 'UTF-8') . '</text></svg>';
        if (file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $svg) === false) {
            throw new RuntimeException('No se pudo guardar ' . $filename);
        }
        DB::table('producto')->where('id_producto', $item->id_producto)->whereNull('imagen')->update(['imagen' => $filename]);
        $saved++;
    }
});
echo "Imágenes asignadas: {$saved}\n";
