import os
import time
from datetime import datetime

import cv2
import face_recognition
import numpy as np
import requests
from PIL import Image

# --- CONFIGURATION ---
PHONE_IP = "192.168.80.122"
BASE_URL = "http://127.0.0.1:8000"
STREAM_URL = f"http://{PHONE_IP}:8080/video"
# Path to the 'public' folder where student subfolders exist
FACES_DIR = (
    r"C:\Users\Tati\Desktop\Software Development\lav_sms\storage\app\public\uploads\student"
    
)

# Generate the session ID for today
CURRENT_SESSION_DATE = datetime.now().strftime("%Y-%m-%d")

known_encodings = []
known_names = []  # This will store the student IDs
last_seen = {}

print(f"--- ATTENDANCE SESSION: {CURRENT_SESSION_DATE} ---")

# 1. FETCH THE MAPPING FROM LARAVEL
# We need to know which image filename belongs to which Student ID
print("Connecting to Laravel for student mapping...")
try:
    map_res = requests.get(f"{BASE_URL}/api/student-map")
    student_map = map_res.json()
except Exception as e:
    print(f"FATAL: Could not get mapping. Is Laravel running? {e}")
    exit()

# 2. LOAD PHOTOS FROM NESTED SUBFOLDERS
# Laravel saves as: student/{code}/photo.jpg
print("Scanning subfolders and encoding faces...")
for root, dirs, files in os.walk(FACES_DIR):
    for filename in files:
        # Check if the file is an image and exists in our mapping
        if filename in student_map:
            try:
                path = os.path.join(root, filename)

                # Load and encode
                img = face_recognition.load_image_file(path)
                encodings = face_recognition.face_encodings(img)

                if len(encodings) > 0:
                    known_encodings.append(encodings[0])
                    # Map the filename back to the Student ID provided by Laravel
                    known_names.append(str(student_map[filename]))
                    print(
                        f"  Successfully encoded: {filename} (ID: {student_map[filename]})"
                    )
            except Exception as e:
                print(f"  Error encoding {filename}: {e}")
                continue

if not known_encodings:
    print("FATAL: No faces were loaded. Check your FACES_DIR path and database.")
    exit()

# 3. LIVE STREAM PROCESSING
print(f"Opening IP Webcam: {STREAM_URL}")
video = cv2.VideoCapture(0)

while True:
    ret, frame = video.read()
    if not ret:
        continue

    # 1/4 size for faster processing
    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb_frame = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb_frame)
    face_encodings = face_recognition.face_encodings(rgb_frame, face_locations)

    for (top, right, bottom, left), face_encoding in zip(
        face_locations, face_encodings
    ):
        matches = face_recognition.compare_faces(
            known_encodings, face_encoding, tolerance=0.5
        )
        name = "Unknown"
        box_color = (0, 0, 255)  # Red

        if True in matches:
            first_match_index = matches.index(True)
            student_id = known_names[first_match_index]
            name = f"Student ID: {student_id}"
            box_color = (0, 255, 0)  # Green

            # COOLDOWN & SEND TO LARAVEL
            curr_time = time.time()
            if student_id not in last_seen or (curr_time - last_seen[student_id] > 30):
                print(f"Marking ID {student_id} for session {CURRENT_SESSION_DATE}...")
                try:
                    payload = {
                        "student_id": student_id,
                        "session_id": CURRENT_SESSION_DATE,
                    }
                    res = requests.post(f"{BASE_URL}/api/mark-attendance", data=payload)

                    if res.status_code in [200, 201]:
                        print(f"  Response: {res.json().get('message')}")
                        last_seen[student_id] = curr_time
                    else:
                        print(f"  Server Error: {res.text}")
                except Exception as e:
                    print(f"  Connection Error: {e}")

        # Draw UI
        top, right, bottom, left = top * 4, right * 4, bottom * 4, left * 4
        cv2.rectangle(frame, (left, top), (right, bottom), box_color, 2)
        cv2.putText(
            frame, name, (left, top - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.7, box_color, 2
        )

    cv2.imshow("Attendance System - Admin Camera", frame)

    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

video.release()
cv2.destroyAllWindows()
