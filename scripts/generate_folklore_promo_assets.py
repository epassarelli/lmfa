from pathlib import Path
from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
BASE = ROOT / "public" / "images" / "copa-folklore-2026"
FINAL = BASE / "final"
FINAL.mkdir(parents=True, exist_ok=True)


def load_font(size: int, bold: bool = False):
    candidates = [
        "C:/Windows/Fonts/arialbd.ttf" if bold else "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/segoeuib.ttf" if bold else "C:/Windows/Fonts/segoeui.ttf",
    ]
    for candidate in candidates:
        if Path(candidate).exists():
            return ImageFont.truetype(candidate, size=size)
    return ImageFont.load_default()


TITLE = load_font(72, bold=True)
SUBTITLE = load_font(38, bold=False)
BODY = load_font(30, bold=False)
BODY_BOLD = load_font(30, bold=True)
SMALL = load_font(22, bold=False)
SMALL_BOLD = load_font(22, bold=True)


def overlay(img: Image.Image, opacity_top=170, opacity_bottom=215):
    shade = Image.new("RGBA", img.size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(shade)
    height = img.size[1]
    for y in range(height):
        alpha = int(opacity_top + (opacity_bottom - opacity_top) * (y / max(1, height - 1)))
        draw.line([(0, y), (img.size[0], y)], fill=(15, 15, 15, alpha))
    return Image.alpha_composite(img.convert("RGBA"), shade)


def draw_multiline(draw, text, xy, font, fill, spacing=10):
    draw.multiline_text(xy, text, font=font, fill=fill, spacing=spacing)


def badge(draw, text, x, y):
    bbox = draw.textbbox((x, y), text, font=SMALL_BOLD)
    pad_x, pad_y = 18, 10
    rect = [bbox[0] - pad_x, bbox[1] - pad_y, bbox[2] + pad_x, bbox[3] + pad_y]
    draw.rounded_rectangle(rect, radius=18, fill=(245, 166, 35, 255))
    draw.text((x, y), text, font=SMALL_BOLD, fill=(24, 20, 16, 255))


def build_slide(input_name: str, output_name: str, eyebrow: str, title: str, body: str, footer: str = ""):
    img = Image.open(BASE / input_name).convert("RGBA")
    img = overlay(img)
    draw = ImageDraw.Draw(img)

    badge(draw, eyebrow, 72, 72)
    draw_multiline(draw, title, (72, 150), TITLE, (255, 248, 235, 255), spacing=4)
    draw_multiline(draw, body, (72, 420), BODY, (240, 233, 220, 255), spacing=12)

    if footer:
        draw.rounded_rectangle((72, 860, 952, 960), radius=24, fill=(255, 255, 255, 36))
        draw_multiline(draw, footer, (102, 890), SMALL_BOLD, (255, 248, 235, 255), spacing=8)

    img.save(FINAL / output_name)


def build_story():
    img = Image.open(BASE / "marketing" / "story-recordatorio-voto-base.png").convert("RGBA")
    img = overlay(img, 145, 225)
    draw = ImageDraw.Draw(img)

    badge(draw, "PROMO OFICIAL", 64, 96)
    draw_multiline(draw, "Ya empezo la\nCopa del Folklore\nArgentino 2026", (64, 180), load_font(86, True), (255, 248, 235, 255), spacing=2)
    draw_multiline(
        draw,
        "32 interpretes.\n8 zonas.\nUna comunidad que decide\npartido a partido.",
        (64, 520),
        load_font(42, False),
        (240, 233, 220, 255),
        spacing=14,
    )
    draw.rounded_rectangle((64, 1020, 656, 1120), radius=28, fill=(245, 166, 35, 255))
    draw.text((110, 1050), "Entra al sitio y mira zonas + fixture", font=load_font(34, True), fill=(24, 20, 16, 255))
    draw.rounded_rectangle((64, 1150, 656, 1265), radius=28, outline=(255, 248, 235, 220), width=3)
    draw_multiline(draw, "Votas en Instagram.\nResultado final en la web.", (112, 1182), load_font(34, True), (255, 248, 235, 255), spacing=10)

    img.save(FINAL / "promo-story-lanzamiento-final.png")


def main():
    build_slide(
        "instagram-slide-01-cover.png",
        "promo-slide-01-portada-final.png",
        "LANZAMIENTO",
        "Copa del\nFolklore\nArgentino 2026",
        "32 interpretes\n8 zonas\nUna comunidad que decide\npartido a partido",
        "Mi Folklore Argentino",
    )

    build_slide(
        "instagram-slide-02-rules.png",
        "promo-slide-02-reglas-final.png",
        "COMO FUNCIONA",
        "Reglas\nbasicas",
        "- 8 zonas de 4 participantes\n- todos contra todos\n- victoria = 3 puntos\n- empate = 1 punto\n- clasifican 2 por zona",
        "La tabla se actualiza con cada resultado final",
    )

    build_slide(
        "instagram-slide-03-fixture.png",
        "promo-slide-03-formato-final.png",
        "FORMATO",
        "Grupos,\nfixture y\nfase final",
        "- fase de grupos\n- octavos de final\n- cuartos de final\n- semifinales\n- tercer puesto\n- final",
        "Segui todo el recorrido en la web",
    )

    build_slide(
        "instagram-slide-04-voting.png",
        "promo-slide-04-votacion-final.png",
        "COMO VOTAR",
        "Votas en\nInstagram",
        "La votacion se realiza\nen la publicacion oficial.\n\nCuando cierre cada cruce,\nel resultado final se carga\nen la web.",
        "Instagram para votar. Sitio para seguir resultados.",
    )

    build_slide(
        "instagram-slide-05-cta.png",
        "promo-slide-05-cta-final.png",
        "SEGUI LA COPA",
        "Consulta\nzonas, fixture\ny resultados",
        "Entra al sitio para ver\nparticipantes, zonas,\nfixture y avance del torneo.",
        "mifolkloreargentino.com/copa-del-folklore-argentino-2026",
    )

    build_story()


if __name__ == "__main__":
    main()
