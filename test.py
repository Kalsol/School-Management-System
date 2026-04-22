import cv2
import os

# 1. TEST IMAGE LOADING

image_path = r"C:\Users\Tati\Desktop\SoftwareDevelopment\lav_sms\storage\app\public\uploads\teacher\CXP4TN3JVP\photo.jpg"  # Replace with a path to a real image on your PC

if os.path.exists(image_path):
    print(f"--- Testing Image: {image_path} ---")
    img = cv2.imread(image_path)
    
    if img is not None:
        print(f"Success! Image size: {img.shape[1]}x{img.shape[0]}")
        cv2.imshow("Static Image Test", img)
        cv2.waitKey(2000) # Show for 2 seconds
        cv2.destroyAllWindows()
    else:
        print("Error: Could not decode image. Format might be unsupported.")
else:
    print(f"Skip: {image_path} not found.")

# 2. TEST WEBCAM
print("\n--- Opening Webcam (Press 'q' to quit) ---")
cap = cv2.VideoCapture(0) # '0' is usually the default laptop camera

if not cap.isOpened():
    print("Error: Could not open webcam.")
else:
    while True:
        ret, frame = cap.read()
        
        if not ret:
            print("Error: Failed to grab frame.")
            break

        # Display the camera feed
        cv2.imshow("Webcam Test", frame)

        # Stop if the user presses the 'q' key
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

cap.release()
cv2.destroyAllWindows()
print("Test complete.")