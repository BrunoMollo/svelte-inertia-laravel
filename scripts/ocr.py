#!/usr/bin/env python3
"""
OCR Script for processing images and PDFs using Tesseract.

Usage:
    python3 ocr.py <file> [--dpi=300] [--lang=spa]

Returns JSON:
    Success: {"success": true, "text": "...", "pages": 1}
    Error: {"success": false, "error": "..."}
"""

import sys
import json
import argparse
import os
from pathlib import Path

try:
    import pytesseract
    from PIL import Image
    from pdf2image import convert_from_path
except ImportError as e:
    print(json.dumps({
        "success": False,
        "error": f"Missing dependency: {e}. Install with: pip install pytesseract Pillow pdf2image"
    }))
    sys.exit(1)


def get_file_extension(file_path: str) -> str:
    """Get lowercase file extension without dot."""
    return Path(file_path).suffix.lower().lstrip('.')


def is_image(extension: str) -> bool:
    """Check if extension is a supported image format."""
    return extension in ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif']


def is_pdf(extension: str) -> bool:
    """Check if extension is PDF."""
    return extension == 'pdf'


def process_image(image_path: str, dpi: int, lang: str) -> tuple[str, int]:
    """
    Process a single image file with OCR.

    Args:
        image_path: Path to the image file
        dpi: DPI setting for OCR
        lang: Language code(s) for Tesseract

    Returns:
        Tuple of (extracted_text, page_count)
    """
    image = Image.open(image_path)

    # Convert to RGB if necessary (handles RGBA, P mode, etc.)
    if image.mode not in ('L', 'RGB'):
        image = image.convert('RGB')

    text = pytesseract.image_to_string(
        image,
        lang=lang,
        config=f'--dpi {dpi}'
    )

    return text.strip(), 1


def process_pdf(pdf_path: str, dpi: int, lang: str) -> tuple[str, int]:
    """
    Process a PDF file with OCR (converts each page to image first).

    Args:
        pdf_path: Path to the PDF file
        dpi: DPI setting for conversion and OCR
        lang: Language code(s) for Tesseract

    Returns:
        Tuple of (extracted_text, page_count)
    """
    images = convert_from_path(pdf_path, dpi=dpi)
    texts = []

    for i, image in enumerate(images, 1):
        # Convert to RGB if necessary
        if image.mode not in ('L', 'RGB'):
            image = image.convert('RGB')

        text = pytesseract.image_to_string(
            image,
            lang=lang,
            config=f'--dpi {dpi}'
        )
        texts.append(text.strip())

    # Join pages with clear separator
    full_text = '\n\n--- Page Break ---\n\n'.join(texts)

    return full_text, len(images)


def main():
    parser = argparse.ArgumentParser(
        description='Process images and PDFs with OCR using Tesseract'
    )
    parser.add_argument(
        'file',
        help='Path to the file to process (image or PDF)'
    )
    parser.add_argument(
        '--dpi',
        type=int,
        default=300,
        help='DPI for OCR processing (default: 300)'
    )
    parser.add_argument(
        '--lang',
        default='spa',
        help='Tesseract language code(s), e.g., spa, eng, spa+eng (default: spa)'
    )

    args = parser.parse_args()

    try:
        # Validate file exists
        if not os.path.exists(args.file):
            raise FileNotFoundError(f"File not found: {args.file}")

        if not os.path.isfile(args.file):
            raise ValueError(f"Path is not a file: {args.file}")

        # Detect file type
        extension = get_file_extension(args.file)

        if is_image(extension):
            text, pages = process_image(args.file, args.dpi, args.lang)
        elif is_pdf(extension):
            text, pages = process_pdf(args.file, args.dpi, args.lang)
        else:
            raise ValueError(
                f"Unsupported file type: .{extension}. "
                f"Supported: jpg, jpeg, png, gif, bmp, tiff, pdf"
            )

        # Output success response
        result = {
            "success": True,
            "text": text,
            "pages": pages
        }
        print(json.dumps(result, ensure_ascii=False))
        sys.exit(0)

    except FileNotFoundError as e:
        result = {"success": False, "error": str(e)}
        print(json.dumps(result, ensure_ascii=False))
        sys.exit(1)

    except Exception as e:
        result = {"success": False, "error": f"{type(e).__name__}: {str(e)}"}
        print(json.dumps(result, ensure_ascii=False))
        sys.exit(1)


if __name__ == '__main__':
    main()
