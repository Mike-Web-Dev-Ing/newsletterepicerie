#!/usr/bin/env python3
"""Generate DOCX and PDF exports from the dossier markdown."""
from __future__ import annotations
import os
import re
import textwrap
import zipfile
from datetime import datetime
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC_MD = ROOT / "docs" / "dossier_newsletterepicerie.md"
OUT_DOCX = ROOT / "docs" / "dossier_newsletterepicerie.docx"
OUT_PDF = ROOT / "docs" / "dossier_newsletterepicerie.pdf"

HEADING_RE = re.compile(r"^(#+)\s+(.*)")
LIST_RE = re.compile(r"^[-*+]\s+(.*)")
NUMBER_RE = re.compile(r"^\d+\.\s+(.*)")
TABLE_RE = re.compile(r"^\|.*\|")
BOLD_RE = re.compile(r"\*\*(.*?)\*\*")
ITALIC_RE = re.compile(r"\*(.*?)\*")
CODE_RE = re.compile(r"`([^`]+)`")


def load_lines() -> list[str]:
    text = SRC_MD.read_text(encoding="utf-8")
    return text.splitlines()


def sanitize(text: str) -> str:
    text = text.replace("\u00a0", " ")
    text = text.replace("\t", " ")
    return text.strip()


def parse_markdown(lines: list[str]):
    """Yield (type, content) tuples."""
    blocks = []
    buffer: list[str] = []

    def flush_paragraph():
        if buffer:
            paragraph = " ".join(line.strip() for line in buffer).strip()
            if paragraph:
                blocks.append(("paragraph", paragraph))
            buffer.clear()

    for raw in lines:
        line = raw.rstrip()
        if not line:
            flush_paragraph()
            continue
        if HEADING_RE.match(line):
            flush_paragraph()
            level, title = HEADING_RE.findall(line)[0]
            blocks.append((f"heading{len(level)}", sanitize(title)))
            continue
        if TABLE_RE.match(line):
            flush_paragraph()
            blocks.append(("table", sanitize(line)))
            continue
        if LIST_RE.match(line):
            flush_paragraph()
            blocks.append(("bullet", sanitize(LIST_RE.findall(line)[0])))
            continue
        if NUMBER_RE.match(line):
            flush_paragraph()
            blocks.append(("number", sanitize(NUMBER_RE.findall(line)[0])))
            continue
        if line.startswith("---"):
            flush_paragraph()
            blocks.append(("separator", ""))
            continue
        buffer.append(line)

    flush_paragraph()
    return blocks


def markdown_inline_to_text(text: str) -> str:
    text = BOLD_RE.sub(lambda m: m.group(1), text)
    text = ITALIC_RE.sub(lambda m: m.group(1), text)
    text = CODE_RE.sub(lambda m: m.group(1), text)
    text = text.replace("\\n", " ")
    return text


# ---------------- DOCX generation ----------------

def xml_escape(text: str) -> str:
    return (
        text.replace("&", "&amp;")
        .replace("<", "&lt;")
        .replace(">", "&gt;")
        .replace("\"", "&quot;")
    )


def build_document_xml(blocks):
    runs = []
    PAGE_BREAK_TAG = "<w:p><w:r><w:br w:type=\"page\"/></w:r></w:p>"
    for block_type, content in blocks:
        text = markdown_inline_to_text(content)
        if block_type == "separator":
            runs.append(PAGE_BREAK_TAG)
            continue
        style = None
        if block_type == "heading1":
            style = "Heading1"
        elif block_type == "heading2":
            style = "Heading2"
        elif block_type == "heading3":
            style = "Heading3"
        elif block_type == "heading4":
            style = "Heading4"
        elif block_type == "bullet":
            style = "ListBullet"
            text = f"• {text}"
        elif block_type == "number":
            style = "ListNumber"
            text = f"– {text}"
        elif block_type == "table":
            style = "TableText"
        else:
            style = "Normal"
        runs.append(make_paragraph(text, style))
    return DOC_XML_TEMPLATE.format(body="".join(runs))


def make_paragraph(text: str, style: str) -> str:
    text = xml_escape(text)
    if style == "TableText":
        text = text.replace("|", " | ")
    para = ["<w:p>"]
    if style and style not in {"Normal", "TableText"}:
        para.append(f"<w:pPr><w:pStyle w:val=\"{style}\"/></w:pPr>")
    if style == "TableText":
        para.append("<w:pPr><w:spacing w:line='240' w:lineRule='auto'/></w:pPr>")
    para.append("<w:r><w:t xml:space='preserve'>" + text + "</w:t></w:r>")
    para.append("</w:p>")
    return "".join(para)


DOC_XML_TEMPLATE = """<?xml version='1.0' encoding='UTF-8'?>
<w:document xmlns:wpc='http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas'
 xmlns:mc='http://schemas.openxmlformats.org/markup-compatibility/2006'
 xmlns:o='urn:schemas-microsoft-com:office:office'
 xmlns:r='http://schemas.openxmlformats.org/officeDocument/2006/relationships'
 xmlns:m='http://schemas.openxmlformats.org/officeDocument/2006/math'
 xmlns:v='urn:schemas-microsoft-com:vml'
 xmlns:wp14='http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing'
 xmlns:wp='http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing'
 xmlns:w10='urn:schemas-microsoft-com:office:word'
 xmlns:w='http://schemas.openxmlformats.org/wordprocessingml/2006/main'
 xmlns:w14='http://schemas.microsoft.com/office/word/2010/wordml'
 xmlns:wpg='http://schemas.microsoft.com/office/word/2010/wordprocessingGroup'
 xmlns:wpi='http://schemas.microsoft.com/office/word/2010/wordprocessingInk'
 xmlns:wne='http://schemas.microsoft.com/office/word/2006/wordml'
 xmlns:wps='http://schemas.microsoft.com/office/word/2010/wordprocessingShape'
 mc:Ignorable='w14 wp14'>
  <w:body>
    {body}
    <w:sectPr>
      <w:pgSz w:w='11906' w:h='16838'/>
      <w:pgMar w:top='1134' w:right='1134' w:bottom='1134' w:left='1134' w:header='708' w:footer='708' w:gutter='0'/>
      <w:cols w:space='708'/>
      <w:docGrid w:linePitch='360'/>
    </w:sectPr>
  </w:body>
</w:document>
"""

STYLES_XML = """<?xml version='1.0' encoding='UTF-8'?>
<w:styles xmlns:w='http://schemas.openxmlformats.org/wordprocessingml/2006/main'>
  <w:style w:type='paragraph' w:default='1' w:styleId='Normal'>
    <w:name w:val='Normal'/>
    <w:rPr>
      <w:rFonts w:ascii='Calibri' w:hAnsi='Calibri'/>
      <w:sz w:val='24'/>
      <w:szCs w:val='24'/>
    </w:rPr>
  </w:style>
  <w:style w:type='paragraph' w:styleId='Heading1'>
    <w:name w:val='heading 1'/>
    <w:basedOn w:val='Normal'/>
    <w:next w:val='Normal'/>
    <w:uiPriority w:val='9'/>
    <w:qFormat/>
    <w:rPr>
      <w:rFonts w:ascii='Calibri' w:hAnsi='Calibri'/>
      <w:b/>
      <w:sz w:val='36'/>
      <w:szCs w:val='36'/>
    </w:rPr>
  </w:style>
  <w:style w:type='paragraph' w:styleId='Heading2'>
    <w:name w:val='heading 2'/>
    <w:basedOn w:val='Normal'/>
    <w:next w:val='Normal'/>
    <w:uiPriority w:val='9'/>
    <w:qFormat/>
    <w:rPr>
      <w:b/>
      <w:sz w:val='30'/>
    </w:rPr>
  </w:style>
  <w:style w:type='paragraph' w:styleId='Heading3'>
    <w:name w:val='heading 3'/>
    <w:basedOn w:val='Normal'/>
    <w:next w:val='Normal'/>
    <w:rPr>
      <w:sz w:val='26'/>
      <w:szCs w:val='26'/>
      <w:i/>
    </w:rPr>
  </w:style>
  <w:style w:type='paragraph' w:styleId='Heading4'>
    <w:name w:val='heading 4'/>
    <w:basedOn w:val='Normal'/>
    <w:next w:val='Normal'/>
    <w:rPr>
      <w:sz w:val='24'/>
      <w:szCs w:val='24'/>
      <w:b/>
      <w:i/>
    </w:rPr>
  </w:style>
  <w:style w:type='paragraph' w:styleId='ListBullet'>
    <w:name w:val='List Bullet'/>
    <w:basedOn w:val='Normal'/>
  </w:style>
  <w:style w:type='paragraph' w:styleId='ListNumber'>
    <w:name w:val='List Number'/>
    <w:basedOn w:val='Normal'/>
  </w:style>
</w:styles>
"""

CORE_XML = """<?xml version='1.0' encoding='UTF-8'?>
<cp:coreProperties xmlns:cp='http://schemas.openxmlformats.org/package/2006/metadata/core-properties' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:dcterms='http://purl.org/dc/terms/' xmlns:dcmitype='http://purl.org/dc/dcmitype/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>
  <dc:title>Dossier Projet – Newsletter Épicerie</dc:title>
  <dc:subject>Projet DWWM</dc:subject>
  <dc:creator>newsletterepicerie</dc:creator>
  <cp:keywords>newsletter, franprix, dwwm</cp:keywords>
  <dc:description>Dossier projet généré automatiquement</dc:description>
  <cp:lastModifiedBy>Codex</cp:lastModifiedBy>
  <dcterms:created xsi:type='dcterms:W3CDTF'>{now}</dcterms:created>
  <dcterms:modified xsi:type='dcterms:W3CDTF'>{now}</dcterms:modified>
</cp:coreProperties>
"""

APP_XML = """<?xml version='1.0' encoding='UTF-8'?>
<Properties xmlns='http://schemas.openxmlformats.org/officeDocument/2006/extended-properties' xmlns:vt='http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes'>
  <Application>Codex Export</Application>
  <DocSecurity>0</DocSecurity>
  <ScaleCrop>false</ScaleCrop>
  <HeadingPairs>
    <vt:vector size='2' baseType='variant'>
      <vt:variant><vt:lpstr>Paragraphs</vt:lpstr></vt:variant>
      <vt:variant><vt:i4>1</vt:i4></vt:variant>
    </vt:vector>
  </HeadingPairs>
  <TitlesOfParts>
    <vt:vector size='1' baseType='lpstr'>
      <vt:lpstr>Dossier</vt:lpstr>
    </vt:vector>
  </TitlesOfParts>
</Properties>
"""

CONTENT_TYPES_XML = """<?xml version='1.0' encoding='UTF-8'?>
<Types xmlns='http://schemas.openxmlformats.org/package/2006/content-types'>
  <Default Extension='rels' ContentType='application/vnd.openxmlformats-package.relationships+xml'/>
  <Default Extension='xml' ContentType='application/xml'/>
  <Override PartName='/word/document.xml' ContentType='application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'/>
  <Override PartName='/word/styles.xml' ContentType='application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml'/>
  <Override PartName='/docProps/core.xml' ContentType='application/vnd.openxmlformats-package.core-properties+xml'/>
  <Override PartName='/docProps/app.xml' ContentType='application/vnd.openxmlformats-officedocument.extended-properties+xml'/>
</Types>
"""

RELS_XML = """<?xml version='1.0' encoding='UTF-8'?>
<Relationships xmlns='http://schemas.openxmlformats.org/package/2006/relationships'>
  <Relationship Id='rId1' Type='http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument' Target='word/document.xml'/>
  <Relationship Id='rId2' Type='http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties' Target='docProps/core.xml'/>
  <Relationship Id='rId3' Type='http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties' Target='docProps/app.xml'/>
</Relationships>
"""

DOCUMENT_RELS_XML = """<?xml version='1.0' encoding='UTF-8'?>
<Relationships xmlns='http://schemas.openxmlformats.org/package/2006/relationships'>
</Relationships>
"""


def generate_docx(blocks):
    now = datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%SZ")
    document_xml = build_document_xml(blocks)
    with zipfile.ZipFile(OUT_DOCX, "w", zipfile.ZIP_DEFLATED) as zf:
        zf.writestr("[Content_Types].xml", CONTENT_TYPES_XML)
        zf.writestr("_rels/.rels", RELS_XML)
        zf.writestr("docProps/core.xml", CORE_XML.format(now=now))
        zf.writestr("docProps/app.xml", APP_XML)
        zf.writestr("word/document.xml", document_xml)
        zf.writestr("word/styles.xml", STYLES_XML)
        zf.writestr("word/_rels/document.xml.rels", DOCUMENT_RELS_XML)


# ---------------- PDF generation ----------------

PDF_HEADER = "%PDF-1.4\n"


def escape_pdf(text: str) -> str:
    return text.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def paragraphs_to_lines(blocks):
    lines = []
    wrap_width = 90
    for block_type, content in blocks:
        if block_type == "separator":
            lines.append(("", "pagebreak"))
            continue
        text = markdown_inline_to_text(content)
        if block_type.startswith("heading"):
            level = int(block_type[-1]) if block_type[-1].isdigit() else 1
            size = {1:18, 2:16, 3:14, 4:13}.get(level, 12)
            wrapped = textwrap.wrap(text.upper(), width=wrap_width) or [text.upper()]
            for line in wrapped:
                lines.append((line, f"heading-{size}"))
            lines.append(("", "spacer"))
        elif block_type == "bullet":
            wrapped = textwrap.wrap(text, width=wrap_width-2) or [text]
            for idx, line in enumerate(wrapped):
                prefix = "• " if idx == 0 else "  "
                lines.append((prefix + line, "body"))
        elif block_type == "number":
            wrapped = textwrap.wrap(text, width=wrap_width-2) or [text]
            for idx, line in enumerate(wrapped):
                prefix = "- " if idx == 0 else "  "
                lines.append((prefix + line, "body"))
        elif block_type == "table":
            lines.append((content, "mono"))
        else:
            wrapped = textwrap.wrap(text, width=wrap_width) or [text]
            for line in wrapped:
                lines.append((line, "body"))
            lines.append(("", "spacer"))
    return lines


def paginate_lines(lines):
    pages = []
    current = []
    max_lines = 48
    count = 0
    for text, kind in lines:
        if kind == "pagebreak":
            pages.append(current)
            current = []
            count = 0
            continue
        if count >= max_lines:
            pages.append(current)
            current = []
            count = 0
        current.append((text, kind))
        if kind not in {"spacer"}:
            count += 1
    if current:
        pages.append(current)
    return pages


def compose_page_stream(page):
    y_start = 800
    leading = 14
    stream_lines = ["BT", "/F1 12 Tf", f"1 0 0 1 72 {y_start} Tm", "12 TL"]
    current_font = 12
    for text, kind in page:
        if kind.startswith("heading"):
            size = int(kind.split("-")[1])
            if size != current_font:
                stream_lines.append(f"/F1 {size} Tf")
                current_font = size
        elif kind == "mono":
            if current_font != 10:
                stream_lines.append("/F1 10 Tf")
                current_font = 10
        else:
            if current_font != 12:
                stream_lines.append("/F1 12 Tf")
                current_font = 12
        text = escape_pdf(text)
        stream_lines.append(f"({text}) Tj")
        stream_lines.append("T*")
    stream_lines.append("ET")
    stream = "\n".join(stream_lines)
    return stream.encode("utf-8")


def generate_pdf(blocks):
    lines = paragraphs_to_lines(blocks)
    pages = paginate_lines(lines)
    objects = []
    xref_positions = []

    def add_object(obj_str: bytes):
        xref_positions.append(len(pdf_parts))
        pdf_parts.extend([f"{len(xref_positions)} 0 obj\n".encode(), obj_str, b"\nendobj\n"])

    pdf_parts = [PDF_HEADER.encode()]
    # Reserve xref positions placeholder to align with object numbers
    xref_positions.append(len(pdf_parts))  # dummy for object 0
    pdf_parts.append(b"")

    kids_refs = []
    content_refs = []

    # Font object
    font_obj = b"<< /Type /Font /Subtype /Type1 /Name /F1 /BaseFont /Helvetica >>"
    add_object(font_obj)  # obj 1

    # Pages placeholder, will fill later
    pages_index = len(xref_positions)
    add_object(b"placeholder")

    # For each page build content stream and page object
    for page in pages:
        stream = compose_page_stream(page)
        stream_obj = f"<< /Length {len(stream)} >>\nstream\n".encode() + stream + b"\nendstream"
        add_object(stream_obj)
        content_ref = len(xref_positions)
        content_refs.append(content_ref)
        page_obj = f"<< /Type /Page /Parent {pages_index} 0 R /Resources << /Font << /F1 1 0 R >> >> /MediaBox [0 0 595 842] /Contents {content_ref} 0 R >>".encode()
        add_object(page_obj)
        kids_refs.append(len(xref_positions))

    # Replace pages object
    kids_array = " ".join(f"{ref} 0 R" for ref in kids_refs)
    pages_obj = f"<< /Type /Pages /Kids [ {kids_array} ] /Count {len(kids_refs)} >>".encode()
    pdf_parts[pdf_parts.index(b"placeholder")] = pages_obj

    # Catalog object
    catalog_obj = f"<< /Type /Catalog /Pages {pages_index} 0 R >>".encode()
    add_object(catalog_obj)

    # Build xref table
    body = b"".join(pdf_parts)
    xref_offset = len(body)
    xref_lines = [b"xref\n", f"0 {len(xref_positions)}\n".encode(), b"0000000000 65535 f \n"]
    accum = 0
    for pos in xref_positions[1:]:
        accum += len(pdf_parts[accum]) if accum < len(pdf_parts) else 0
        xref_lines.append(f"{pos:010d} 00000 n \n".encode())
    trailer = (
        b"trailer\n" +
        f"<< /Size {len(xref_positions)} /Root {len(xref_positions)} 0 R >>\n".encode() +
        b"startxref\n" +
        f"{xref_offset}\n".encode() +
        b"%%EOF"
    )
    with open(OUT_PDF, "wb") as fh:
        fh.write(body)
        fh.writelines(xref_lines)
        fh.write(trailer)


def main():
    if not SRC_MD.exists():
        raise SystemExit(f"Source markdown introuvable: {SRC_MD}")
    lines = load_lines()
    blocks = parse_markdown(lines)
    OUT_DOCX.parent.mkdir(parents=True, exist_ok=True)
    generate_docx(blocks)
    generate_pdf(blocks)
    print(f"DOCX -> {OUT_DOCX}")
    print(f"PDF  -> {OUT_PDF}")


if __name__ == "__main__":
    main()
