import os
import time
from datetime import datetime
import cv2
import face_recognition
import numpy as np
import requests
from PIL import Image

# --- CONFIGURATION ---
PHONE_IP = "192.168.80.43"
BASE_URL = "http://127.0.0.1:8000"
# If using IP Webcam app, usually it's :8080/video. For local webcam, use 0
STREAM_URL = 0 
FACES_DIR = r"C:\Users\Tati\Desktop\SoftwareDevelopment\lav_sms\storage\app\public\uploads\teacher"

# Generate the session ID for today
CURRENT_SESSION_DATE = datetime.now().strftime("%Y-%m-%d")

known_encodings = []
known_names = []  # Stores Teacher IDs
last_seen = {}

print(f"--- ATTENDANCE SESSION: {CURRENT_SESSION_DATE} ---")

# 1. FETCH THE MAPPING FROM LARAVEL
print("Connecting to Laravel for teacher mapping...")
try:
    # Adding a timeout to prevent the script from hanging forever
    map_res = requests.get(f"{BASE_URL}/api/teacher-map", timeout=10)
    teacher_map = map_res.json()
except Exception as e:
    print(f"FATAL: Could not get mapping. Is Laravel running? {e}")
    exit()

# 2. LOAD PHOTOS FROM NESTED SUBFOLDERS
print("Scanning subfolders and encoding faces...")
for root, dirs, files in os.walk(FACES_DIR):
    for filename in files:
        # Only process common image extensions
        if not filename.lower().endswith(('.png', '.jpg', '.jpeg', '.webp')):
            continue

        if filename in teacher_map:
            try:
                path = os.path.join(root, filename)

                # --- THE FIX: Normalize Image using PIL ---
                # This converts any weird formats (RGBA, CMYK) to standard 8-bit RGB
                pil_img = Image.open(path).convert('RGB')
                img_array = np.array(pil_img)

                # Find face encodings
                encodings = face_recognition.face_encodings(img_array)

                if len(encodings) > 0:
                    known_encodings.append(encodings[0])
                    known_names.append(str(teacher_map[filename]))
                    print(f"✅ Encoded: {filename} (ID: {teacher_map[filename]})")
                else:
                    print(f"⚠️  No face found in: {filename}")

            except Exception as e:
                print(f"❌ Error processing {filename}: {e}")
                continue

if not known_encodings:
    print("FATAL: No faces were loaded. Check your images and FACES_DIR.")
    exit()

# 3. LIVE STREAM PROCESSING
print(f"Opening Camera...")
video = cv2.VideoCapture(STREAM_URL)

while True:
    ret, frame = video.read()
    if not ret:
        print("Failed to grab frame from camera.")
        break

    # Process a smaller frame (1/4 size) for much better CPU performance
    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb_small_frame = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    # Find faces in current frame
    face_locations = face_recognition.face_locations(rgb_small_frame)
    face_encodings = face_recognition.face_encodings(rgb_small_frame, face_locations)

    for (top, right, bottom, left), face_encoding in zip(face_locations, face_encodings):
        # Compare face with known encodings
        matches = face_recognition.compare_faces(known_encodings, face_encoding, tolerance=0.5)
        name = "Unknown"
        box_color = (0, 0, 255) # Red for unknown

        if True in matches:
            first_match_index = matches.index(True)
            teacher_id = known_names[first_match_index]
            name = f"Teacher ID: {teacher_id}"
            box_color = (0, 255, 0) # Green for recognized

            # COOLDOWN LOGIC (30 seconds)
            curr_time = time.time()
            if teacher_id not in last_seen or (curr_time - last_seen[teacher_id] > 30):
                print(f"🚀 Marking Attendance for ID: {teacher_id}...")
                
                try:
                    payload = {
                        "teacher_id": teacher_id,
                        "session_id": CURRENT_SESSION_DATE,
                    }
                    res = requests.post(f"{BASE_URL}/api/mark-attendance", data=payload, timeout=5)

                    if res.status_code in [200, 201]:
                        print(f"  Done: {res.json().get('message', 'Success')}")
                        last_seen[teacher_id] = curr_time
                    else:
                        print(f"  Server Error ({res.status_code}): {res.text}")
                except Exception as e:
                    print(f"  Network Error: {e}")

        # Scale back up face locations since we processed at 1/4 size
        top *= 4
        right *= 4
        bottom *= 4
        left *= 4

        # Draw the box and label
        cv2.rectangle(frame, (left, top), (right, bottom), box_color, 2)
        cv2.rectangle(frame, (left, bottom - 35), (right, bottom), box_color, cv2.FILLED)
        cv2.putText(frame, name, (left + 6, bottom - 6), cv2.FONT_HERSHEY_DUPLEX, 0.6, (255, 255, 255), 1)

    # Display the result
    cv2.imshow("SMS Attendance System", frame)

    # Press 'q' to quit
    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

# Cleanup
video.release()
cv2.destroyAllWindows()