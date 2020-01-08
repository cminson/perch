import os
import sys
import random
import math
import numpy as np
from PIL import Image
import skimage.io
import cv2

if __name__ == '__main__':

    if len(sys.argv) != 7:
        print('usage: srcImage srcMask dstImage outputImage centerX centerY')
        exit()

    PATH_SRC = sys.argv[1] 
    PATH_MASK = sys.argv[2]
    PATH_DST = sys.argv[3]
    PATH_OUTPUT = sys.argv[4]
    centerX = int(sys.argv[5])
    centerY = int(sys.argv[6])
    print(PATH_MASK)


    src = cv2.imread(PATH_SRC)
    mask = cv2.imread(PATH_MASK)
    dst = cv2.imread(PATH_DST)
    center = (centerX, centerY)

    rows = mask.shape[0]
    cols = mask.shape[1]
    print("width: ",rows, "height:", cols)

    for row in range(0, rows):
        for col in range(0, cols):
            #print(row, col)
            bits = mask[row, col]
            if bits[0] > 0 or bits[1] > 0 or bits[2] > 0:
                mask[row, col] = [255, 255, 255]

    result_image = cv2.seamlessClone(src, dst, mask, center, cv2.NORMAL_CLONE)
    print(PATH_OUTPUT)
    cv2.imwrite(PATH_OUTPUT, result_image);





