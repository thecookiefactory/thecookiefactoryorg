from PIL import Image
import sys
import os
import re

TARGET_WIDTH = 900

def saveImage(img, suffix):
    if isinstance(suffix, str):
        suffix = [suffix]
    for s in suffix:
        img.save(sys.argv[2] + re.match('.+[\\\/](.*)\.\w+$', sys.argv[1]).group(1) + s)


def main():
    ext = sys.argv[3]
    os.rename(sys.argv[1], sys.argv[1] + '.' + ext)
    sys.argv[2] = sys.argv[2].replace('"', '')
    try:
        img = Image.open(sys.argv[1] + '.' + ext)
    except IndexError:
        sys.exit('No input file specified.')
    except OSError:
        sys.exit('Invalid input file.')
    try:
        os.makedirs(sys.argv[2])
    except FileExistsError:
        pass
    try:
        saveImage(img, ['.full.png', '.full.webp'])

        if TARGET_WIDTH < img.size[0]:
            wpercent = (TARGET_WIDTH / float(img.size[0]))
            hsize = int((float(img.size[1]) * float(wpercent)))
            img = img.resize((TARGET_WIDTH, hsize), Image.ANTIALIAS)

        saveImage(img, ['.png', '.webp'])
    except PermissionError:
        sys.exit('No sufficient permissions to save output file.')


if __name__ == '__main__':
    main()
