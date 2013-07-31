from PIL import Image
import sys


def main():
    image = Image.open(sys.argv[1])
    image.save(sys.argv[1].rsplit('.', 1)[0] + '.webp')


if __name__ == '__main__':
    main()
