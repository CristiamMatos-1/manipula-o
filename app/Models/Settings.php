<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Textos e imagens editáveis da página inicial (tabela site_settings).
 * Sem registro salvo, vale o texto padrão. Nos textos, {empresa}, {slogan}, {ano} e {anos} são substituídos
 * pelos dados da empresa.
 */
final class Settings
{
    public const GROUPS = [
        'banner' => ['Banner de destaque', 'Aparece no topo da página inicial SOMENTE se você preencher algo (imagem, título ou texto). Tudo vazio = banner oculto.'],
        'hero' => ['Topo (Hero)', 'Primeira área da página, ao lado do vídeo institucional (o vídeo é configurado em Empresa). Deixe o texto do botão vazio para ocultá-lo.'],
        'about' => ['Sobre nós', 'Inclui o contador de anos de mercado (calculado pelo ano de fundação) e os três destaques.'],
        'articles' => ['Seção de artigos', 'Lista dos últimos artigos publicados (some sozinha se não houver artigos).'],
        'contact' => ['Contato', 'Telefone, e-mail, endereço e mapa vêm de Empresa.'],
        'feed' => ['Novidades (páginas)', 'Feed de páginas publicadas, logo depois do Contato.'],
        'footer' => ['Rodapé', 'Os dados legais (razão social, CNPJ etc.) são editados em Empresa.'],
        'seo' => ['Google (SEO da página inicial)', 'Título e descrição que aparecem nos resultados de busca.'],
    ];

    /** tipo: text | textarea | url | image */
    public const DEFS = [
        'banner_image' => ['group' => 'banner', 'type' => 'image', 'default' => '', 'label' => 'Imagem do banner'],
        'banner_title' => ['group' => 'banner', 'type' => 'text', 'default' => '', 'max' => 120, 'label' => 'Título do banner'],
        'banner_text' => ['group' => 'banner', 'type' => 'textarea', 'default' => '', 'max' => 300, 'label' => 'Texto do banner'],
        'banner_link_label' => ['group' => 'banner', 'type' => 'text', 'default' => '', 'max' => 40, 'label' => 'Texto do botão do banner', 'hint' => 'Ex.: Saiba mais. Se vazio e houver link, usa "Saiba mais".'],
        'banner_link_url' => ['group' => 'banner', 'type' => 'url', 'default' => '', 'max' => 500, 'label' => 'Link do banner', 'hint' => 'https://... ou um endereço do site, ex.: /blog ou /p/promocao'],

        'hero_badge' => ['group' => 'hero', 'type' => 'text', 'default' => '{slogan}', 'max' => 80, 'label' => 'Etiqueta acima do título'],
        'hero_title' => ['group' => 'hero', 'type' => 'text', 'default' => 'Cuidado sob medida,', 'max' => 120, 'label' => 'Título (parte normal)'],
        'hero_highlight' => ['group' => 'hero', 'type' => 'text', 'default' => 'fórmula por fórmula.', 'max' => 120, 'label' => 'Título (parte em destaque verde)'],
        'hero_text' => ['group' => 'hero', 'type' => 'textarea', 'default' => 'Na {empresa}, cada medicamento é manipulado de forma individualizada, com rigor técnico e atendimento farmacêutico próximo.', 'max' => 400, 'label' => 'Texto de apresentação'],
        'hero_btn1_label' => ['group' => 'hero', 'type' => 'text', 'default' => 'Fale conosco', 'max' => 40, 'label' => 'Botão 1 - texto'],
        'hero_btn1_url' => ['group' => 'hero', 'type' => 'url', 'default' => '/#contato', 'max' => 500, 'label' => 'Botão 1 - link'],
        'hero_btn2_label' => ['group' => 'hero', 'type' => 'text', 'default' => 'Conheça a {empresa}', 'max' => 40, 'label' => 'Botão 2 - texto'],
        'hero_btn2_url' => ['group' => 'hero', 'type' => 'url', 'default' => '/#sobre', 'max' => 500, 'label' => 'Botão 2 - link'],
        'hero_video_placeholder' => ['group' => 'hero', 'type' => 'text', 'default' => 'O vídeo institucional será exibido aqui.', 'max' => 120, 'label' => 'Aviso quando não há vídeo'],

        'about_label' => ['group' => 'about', 'type' => 'text', 'default' => 'Sobre nós', 'max' => 40, 'label' => 'Etiqueta da seção'],
        'about_title' => ['group' => 'about', 'type' => 'text', 'default' => 'Tradição e confiança desde {ano}', 'max' => 120, 'label' => 'Título'],
        'about_counter_text' => ['group' => 'about', 'type' => 'text', 'default' => 'anos de mercado cuidando da saúde de gerações de famílias.', 'max' => 160, 'label' => 'Texto ao lado do contador de anos'],
        'about_text' => ['group' => 'about', 'type' => 'textarea', 'default' => 'A {empresa} é uma farmácia de manipulação que une conhecimento científico, matéria-prima selecionada e atendimento humanizado. Cada fórmula é preparada sob medida, seguindo rigorosos controles de qualidade.', 'max' => 1500, 'label' => 'Texto principal'],
        'about_f1_title' => ['group' => 'about', 'type' => 'text', 'default' => 'Fórmulas sob medida', 'max' => 40, 'label' => 'Destaque 1 - título'],
        'about_f1_text' => ['group' => 'about', 'type' => 'text', 'default' => 'Dose e forma ideais para você.', 'max' => 100, 'label' => 'Destaque 1 - texto'],
        'about_f2_title' => ['group' => 'about', 'type' => 'text', 'default' => 'Qualidade', 'max' => 40, 'label' => 'Destaque 2 - título'],
        'about_f2_text' => ['group' => 'about', 'type' => 'text', 'default' => 'Controle em cada etapa.', 'max' => 100, 'label' => 'Destaque 2 - texto'],
        'about_f3_title' => ['group' => 'about', 'type' => 'text', 'default' => 'Atendimento', 'max' => 40, 'label' => 'Destaque 3 - título'],
        'about_f3_text' => ['group' => 'about', 'type' => 'text', 'default' => 'Orientação farmacêutica próxima.', 'max' => 100, 'label' => 'Destaque 3 - texto'],
        'pharmacist_title' => ['group' => 'about', 'type' => 'text', 'default' => 'Farmacêutico(a) Responsável', 'max' => 60, 'label' => 'Título do cartão do farmacêutico'],

        'articles_title' => ['group' => 'articles', 'type' => 'text', 'default' => 'Últimos artigos', 'max' => 80, 'label' => 'Título da seção'],
        'articles_link_label' => ['group' => 'articles', 'type' => 'text', 'default' => 'Ver todos', 'max' => 40, 'label' => 'Texto do link para todos os artigos'],

        'contact_label' => ['group' => 'contact', 'type' => 'text', 'default' => 'Contato', 'max' => 40, 'label' => 'Etiqueta da seção'],
        'contact_title' => ['group' => 'contact', 'type' => 'text', 'default' => 'Venha nos visitar ou fale conosco', 'max' => 120, 'label' => 'Título'],
        'contact_whatsapp_label' => ['group' => 'contact', 'type' => 'text', 'default' => 'Chamar no WhatsApp', 'max' => 40, 'label' => 'Texto do botão de WhatsApp'],
        'whatsapp_message' => ['group' => 'contact', 'type' => 'text', 'default' => 'Olá! Gostaria de falar com a {empresa}.', 'max' => 200, 'label' => 'Mensagem inicial do WhatsApp'],

        'feed_title' => ['group' => 'feed', 'type' => 'text', 'default' => 'Novidades', 'max' => 80, 'label' => 'Título da seção'],

        'footer_text' => ['group' => 'footer', 'type' => 'textarea', 'default' => '', 'max' => 300, 'label' => 'Texto livre no rodapé (opcional)', 'hint' => 'Ex.: horário de funcionamento. Aparece abaixo do nome da empresa.'],

        'home_seo_title' => ['group' => 'seo', 'type' => 'text', 'default' => '{empresa} | {slogan} desde {ano}', 'max' => 70, 'label' => 'Título no Google'],
        'home_seo_description' => ['group' => 'seo', 'type' => 'textarea', 'default' => '{empresa}: farmácia de manipulação com mais de três décadas de tradição, qualidade e atendimento farmacêutico especializado.', 'max' => 160, 'label' => 'Descrição no Google'],
    ];

    private static ?array $saved = null;

    private static function saved(): array
    {
        if (self::$saved === null) {
            self::$saved = [];
            foreach (Database::all('SELECT skey, svalue FROM site_settings') as $row) {
                self::$saved[$row['skey']] = (string) $row['svalue'];
            }
        }
        return self::$saved;
    }

    /** Valor salvo (ou padrão) sem substituir variáveis; usado para preencher o formulário. */
    public static function raw(string $key): string
    {
        $saved = self::saved();
        return array_key_exists($key, $saved) ? $saved[$key] : (string) (self::DEFS[$key]['default'] ?? '');
    }

    /** Texto pronto para exibir (variáveis substituídas). Escape com e() na saída. */
    public static function get(string $key, ?array $company = null): string
    {
        $company ??= Company::get();
        return strtr(self::raw($key), [
            '{empresa}' => (string) ($company['name'] ?? 'Drogacare'),
            '{slogan}' => (string) ($company['tagline'] ?? ''),
            '{ano}' => (string) ($company['founded_year'] ?? ''),
            '{anos}' => (string) Company::yearsInBusiness((int) ($company['founded_year'] ?? date('Y'))),
        ]);
    }

    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (!isset(self::DEFS[$key])) {
                continue;
            }
            Database::run(
                'INSERT INTO site_settings (skey, svalue, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue), updated_at = NOW()',
                [$key, $value]
            );
        }
        self::$saved = null;
    }

    /** Volta todos os textos (menos o banner) ao padrão. */
    public static function resetTexts(): void
    {
        $keys = array_values(array_filter(array_keys(self::DEFS), static fn ($k) => !str_starts_with($k, 'banner_')));
        Database::run('DELETE FROM site_settings WHERE skey IN (' . implode(',', array_fill(0, count($keys), '?')) . ')', $keys);
        self::$saved = null;
    }

    public static function hasBanner(): bool
    {
        return self::raw('banner_image') !== '' || trim(self::raw('banner_title')) !== '' || trim(self::raw('banner_text')) !== '';
    }
}
