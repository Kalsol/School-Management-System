import os
import time
from datetime import datetime

import cv2
import face_recognition
import numpy as np
import requests
from PIL import Image

# ---------------- CONFIG ---------------- #
PHONE_IP = "10.47.116.232"
BASE_URL = "http://127.0.0.1:8000"

STREAM_URL = f"http://{PHONE_IP}:8080/video"

FACES_DIR = r"C:\Users\Tati\Desktop\Software Development\lav_sms\storage\app\public\uploads\student"

CURRENT_SESSION_DATE = datetime.now().strftime("%Y-%m-%d")

# ---------------------------------------- #

known_encodings = []
known_names = []
last_seen = {}

print(f"\n--- ATTENDANCE SESSION: {CURRENT_SESSION_DATE} ---")

# 🔥 1. GET STUDENT MAP
print("Connecting to Laravel...")
try:
    res = requests.get(f"{BASE_URL}/api/student-map")
    student_map = res.json()
    print("Student map loaded:", student_map)
except Exception as e:
    print("FATAL: Laravel connection failed:", e)
    exit()

# 🔥 2. LOAD & ENCODE FACES
print("\nScanning student folders...")

for root, dirs, files in os.walk(FACES_DIR):
    for filename in files:

        path = os.path.join(root, filename)

        # Get folder name (STU001)
        student_code = os.path.basename(root)

        if student_code not in student_map:
            continue

        try:
            # 🔥 FIX IMAGE FORMAT
            pil_image = Image.open(path).convert("RGB")
            img = np.array(pil_image)

            encodings = face_recognition.face_encodings(img)

            if len(encodings) > 0:
                known_encodings.append(encodings[0])
                known_names.append(str(student_map[student_code]))

                print(f"✔ Encoded {student_code}")

        except Exception as e:
            print(f"❌ Error with {filename}: {e}")

if not known_encodings:
    print("FATAL: No faces loaded!")
    exit()

print(f"\nLoaded {len(known_encodings)} faces successfully.\n")

# 🔥 3. CAMERA START
video = cv2.VideoCapture(STREAM_URL)

print("Camera started... Press 'q' to quit.\n")

while True:
    ret, frame = video.read()
    if not ret:
        continue

    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb_frame = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb_frame)
    face_encodings = face_recognition.face_encodings(rgb_frame, face_locations)

    for (top, right, bottom, left), face_encoding in zip(face_locations, face_encodings):

        matches = face_recognition.compare_faces(known_encodings, face_encoding, tolerance=0.5)

        name = "Unknown"
        color = (0, 0, 255)

        if True in matches:
            index = matches.index(True)
            student_id = known_names[index]

            name = f"ID: {student_id}"
            color = (0, 255, 0)

            current_time = time.time()

            if student_id not in last_seen or (current_time - last_seen[student_id] > 30):

                print(f"Marking {student_id}...")

                try:
                    payload = {
                        "student_id": student_id,
                        "session_id": CURRENT_SESSION_DATE,
                    }

                    response = requests.post(
                        f"{BASE_URL}/api/mark-attendance",
                        data=payload
                    )

                    print("Server:", response.text)

                    last_seen[student_id] = current_time

                except Exception as e:
                    print("API Error:", e)

        # Draw box
        top *= 4
        right *= 4
        bottom *= 4
        left *= 4

        cv2.rectangle(frame, (left, top), (right, bottom), color, 2)
        cv2.putText(frame, name, (left, top - 10),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.7, color, 2)

    cv2.imshow("Attendance System", frame)

    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

video.release()
cv2.destroyAllWindows()