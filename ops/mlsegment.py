#!/usr/bin/python3

# Author:  Christopher Minson
#
#
import os
import sys
import random
import math
import numpy as np
import skimage.io
import matplotlib
import matplotlib.pyplot as plt
from PIL import Image
import logging
LOG_FILENAME = '/var/www/perch/ops/python.log'
logging.basicConfig(filename=LOG_FILENAME,level=logging.DEBUG)

import warnings
warnings.filterwarnings("ignore")

MODEL_PATH = '/var/www/perch/MODELS/mask_rcnn/mask_rcnn_coco.hy'
MODEL_WEIGHTS_PATH = '/var/www/perch/MODELS/mask_rcnn/mask_rcnn_coco.h5'
MODEL_DIR = '/var/www/perch/MODELS/Mask_RCNN'
COCO_DIR = '/var/www/perch/MODELS/Mask_RCNN/samples/coco'

COCO_CLASS_NAMES = ['BG', 'person', 'bicycle', 'car', 'motorcycle', 'airplane',
               'bus', 'train', 'truck', 'boat', 'traffic light',
               'fire hydrant', 'stop sign', 'parking meter', 'bench', 'bird',
               'cat', 'dog', 'horse', 'sheep', 'cow', 'elephant', 'bear',
               'zebra', 'giraffe', 'backpack', 'umbrella', 'handbag', 'tie',
               'suitcase', 'frisbee', 'skis', 'snowboard', 'sports ball',
               'kite', 'baseball bat', 'baseball glove', 'skateboard',
               'surfboard', 'tennis racket', 'bottle', 'wine glass', 'cup',
               'fork', 'knife', 'spoon', 'bowl', 'banana', 'apple',
               'sandwich', 'orange', 'broccoli', 'carrot', 'hot dog', 'pizza',
               'donut', 'cake', 'chair', 'couch', 'potted plant', 'bed',
               'dining table', 'toilet', 'tv', 'laptop', 'mouse', 'remote',
               'keyboard', 'cell phone', 'microwave', 'oven', 'toaster',
               'sink', 'refrigerator', 'book', 'clock', 'vase', 'scissors',
               'teddy bear', 'hair drier', 'toothbrush']


#
# generate the background region mask
# this is simply the negative of the sum of all other region masks
#
def computeBackgroundRegion(file_name,regionFileList):

    if len(regionFileList) == 0: return

    image_region = Image.open(regionFileList[0])
    background_bitmap = np.array(image_region)
    width, height = image_region.size
    object_name = 'background'
    score = '99'
    x = y = 0

    for regionFile in regionFileList:

        image_region = Image.open(regionFile)
        bitmap = np.array(image_region)
        background_bitmap = np.logical_or(background_bitmap, bitmap).astype(np.uint8)

    background_bitmap[background_bitmap == 1] = 255
    background_inverted_bitmap = np.invert(background_bitmap, dtype=np.uint8)
    """
    print(np.count_nonzero(background_bitmap == 255))
    print(np.count_nonzero(background_bitmap == 1))
    print(np.count_nonzero(background_bitmap == 0))
    """

    file_name = f'../CONVERSIONS/m{file_name}.{score}.{object_name}.{x}_{y}_{width}_{height}.png'
    image_background = Image.fromarray(background_inverted_bitmap, 'L')
    image_background.save(file_name, 'PNG')


if __name__ == '__main__':

    count = len(sys.argv)
    if count != 2:
        print('ERROR', end='')
        exit()

    inputFilePath = sys.argv[1]
    file_name =  os.path.basename(inputFilePath).split('.')[0]
    #print(file_name)

    # Locally import Mask RCNN and coco
    sys.path.append(MODEL_DIR)
    sys.path.append(COCO_DIR)
    from mrcnn import utils
    import mrcnn.model as modellib
    import coco

    class InferenceConfig(coco.CocoConfig):
        # Set batch size to 1 since we'll be running inference on
        # one image at a time. Batch size = GPU_COUNT * IMAGES_PER_GPU
        GPU_COUNT = 1
        IMAGES_PER_GPU = 1

    config = InferenceConfig()
    #config.display()

    # Create model object in inference mode, fold in weights
    model = modellib.MaskRCNN(mode="inference", model_dir=MODEL_PATH, config=config)
    model.load_weights(MODEL_WEIGHTS_PATH, by_name=True)

    image_input = Image.open(inputFilePath)
    input_width, input_height = image_input.size
    image_input = skimage.io.imread(inputFilePath)
    results = model.detect([image_input], verbose=1)

    result = results[0]
    class_ids = result['class_ids']
    masks = result['masks']
    masks = masks.astype(np.uint8)
    scores = result['scores']
    rois = result['rois']

    regionFileList = []
    for index, class_id in enumerate(class_ids):

        object_name = COCO_CLASS_NAMES[class_id].replace(' ', '_')
        score = scores[index]
        score = int(score * 100)
        roi = rois[index]
        print('ROI:', type(roi), roi)

        bitmap = masks[:,:,index]
        bitmap[bitmap > 0] = 255
        print(roi)
        #print(bitmap.shape)

        y1  = roi[0]
        x1 = roi[1]
        y2 = roi[2]
        x2 = roi[3]
        width = x2 - x1
        height = y2 - y1

        outputFilePath = f'../CONVERSIONS/m{file_name}.{score}.{object_name}.{x1}_{y1}_{width}_{height}.png'
        #print(outputFilePath)
        image_region = Image.fromarray(bitmap, 'L')
        image_region.save(outputFilePath, 'PNG')

        #logging.debug(f'{outputFilePath}')
        regionFileList.append(outputFilePath)

        if object_name == 'person':
            print('person bitmap seen')
            person_bitmap = bitmap


    computeBackgroundRegion(file_name, regionFileList)



import cv2
cascade = '/usr/local/lib/python3.6/dist-packages/cv2/data'
cascPath = '/usr/local/lib/python3.6/dist-packages/cv2/data/haarcascade_frontalface_default.xml'
eyePath = '/usr/local/lib/python3.6/dist-packages/cv2/data/haarcascade_eye.xml'
smilePath = '/usr/local/lib/python3.6/dist-packages/cv2/data/haarcascade_smile.xml'
faceCascade = cv2.CascadeClassifier(cascPath)
eyeCascade = cv2.CascadeClassifier(eyePath)
smileCascade = cv2.CascadeClassifier(smilePath)

object_name = 'face'
score = 99

faces = faceCascade.detectMultiScale(
image_input,
scaleFactor=1.1,
minNeighbors=5,
flags=cv2.CASCADE_SCALE_IMAGE
)
for (x, y, w, h) in faces:
    outputFilePath = f'../CONVERSIONS/m{file_name}.{score}.{object_name}.{x}_{y}_{w}_{h}.png'
    print(outputFilePath)
    bitmap = np.zeros([input_height, input_width], dtype = np.uint8)
    #bitmap[bitmap > 0] = 255

    #testing
    """
    x = x - 20
    y = y - 20
    w = w + 40
    h = h + 40
    """
    bitmap[y:y+h, x:x+w] = 255
    face_bitmap = np.logical_and(person_bitmap, bitmap).astype(np.uint8)
    face_bitmap[face_bitmap == 1] = 255
    image_face = Image.fromarray(face_bitmap, 'L')
    image_face.save(outputFilePath, 'PNG')





