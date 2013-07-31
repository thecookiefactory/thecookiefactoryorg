from PIL import Image
import sys


def main():
    try:
        image = Image.open(sys.argv[1])
    except IndexError:
        sys.exit('No input file specified.')
    except OSError:
        sys.exit('Invalid input file.')
    try:
        image.save(sys.argv[1].rsplit('.', 1)[0] + '.webp')
    except PermissionError:
        sys.exit('No sufficient permissions to save output file.')

if __name__ == '__main__':
    main()
