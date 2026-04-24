import os
import time
from datetime import datetime

import cv2
import face_recognition
import numpy as np
import requests

# ---------------- CONFIG ---------------- #
PHONE_IP = "10.47.116.232"
BASE_URL = "http://127.0.0.1:8000"

STREAM_URL = f"http://{PHONE_IP}:8080/video"

# Use raw string for Windows paths to avoid escape character issues
FACES_DIR = r"C:\Users\Tati\Desktop\SoftwareDevelopment\lav_sms\storage\app\public\uploads\student"

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
        # Only process image files
        if not filename.lower().endswith(('.png', '.jpg', '.jpeg')):
            continue

        path = os.path.join(root, filename)

        # Get folder name (e.g., STU001)
        student_code = os.path.basename(root)

        if student_code not in student_map:
            continue

        try:
            # 🔥 USE OPENCV INSTEAD OF PIL
            # cv2.imread loads images as BGR by default
            bgr_img = cv2.imread(path)
            
            if bgr_img is None:
                print(f"❌ Could not read image: {filename}")
                continue

            # Convert to RGB (required by face_recognition)
            rgb_img = cv2.cvtColor(bgr_img, cv2.COLOR_BGR2RGB)
            
            # Final safety cast to 8-bit
            img_final = np.array(rgb_img, dtype=np.uint8)

            encodings = face_recognition.face_encodings(img_final)

            if len(encodings) > 0:
                known_encodings.append(encodings[0])
                known_names.append(str(student_map[student_code]))
                print(f"✔ Encoded {student_code}")
            else:
                print(f"⚠️ No face found in {filename}")

        except Exception as e:
            print(f"❌ Error with {filename}: {e}")

if not known_encodings:
    print("FATAL: No faces loaded! Check your image directory and formats.")
    exit()

print(f"\nLoaded {len(known_encodings)} faces successfully.\n")

# 🔥 3. CAMERA START
video = cv2.VideoCapture(0)

print("Camera started... Press 'q' to quit.\n")

while True:
    ret, frame = video.read()
    if not ret:
        print("Failed to grab frame from stream.")
        time.sleep(1)
        continue

    # Resize for faster processing
    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb_frame = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb_frame)
    face_encodings = face_recognition.face_encodings(rgb_frame, face_locations)

    for (top, right, bottom, left), face_encoding in zip(face_locations, face_encodings):

        # Lower tolerance = stricter matching (0.5 is a good balance)
        matches = face_recognition.compare_faces(known_encodings, face_encoding, tolerance=0.5)

        name = "Unknown"
        color = (0, 0, 255)

        if True in matches:
            index = matches.index(True)
            student_id = known_names[index]

            name = f"ID: {student_id}"
            color = (0, 255, 0)

            current_time = time.time()

            # Prevent spamming the API (30-second cooldown per student)
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

                    print("Server Response:", response.status_code)
                    last_seen[student_id] = current_time

                except Exception as e:
                    print("API Error:", e)

        # Scale back up face locations since the frame we detected in was 1/4 size
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