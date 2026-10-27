"""jjonglive.html 을 프레임별로 캡처해서 jjonglive.mp4 를 만든다.
사용법: python3 make_video.py <프레임 저장 폴더> <출력 mp4>
필요: playwright(chromium), ffmpeg, Noto Sans CJK KR, Noto Color Emoji 글꼴
"""
import os, subprocess, sys
from playwright.sync_api import sync_playwright

FPS, SEC = 24, 60
frames, out = sys.argv[1], sys.argv[2]
html = 'file://' + os.path.abspath(os.path.join(os.path.dirname(__file__), 'jjonglive.html'))
os.makedirs(frames, exist_ok=True)

with sync_playwright() as p:
    b = p.chromium.launch()
    pg = b.new_page(viewport={'width': 1280, 'height': 720})
    pg.goto(html); pg.wait_for_timeout(800)
    for i in range(FPS * SEC):
        pg.evaluate(f'render({i / FPS})')
        pg.screenshot(path=f'{frames}/f{i:04d}.jpg', type='jpeg', quality=92)
    b.close()

# H.264 + 무음 AAC, 2초마다 키프레임(HLS 조각 2초와 맞춤)
subprocess.run(['ffmpeg', '-y', '-loglevel', 'error',
    '-framerate', str(FPS), '-i', f'{frames}/f%04d.jpg',
    '-f', 'lavfi', '-i', 'anullsrc=r=44100:cl=stereo',
    '-t', str(SEC), '-map', '0:v', '-map', '1:a',
    '-c:v', 'libx264', '-preset', 'veryslow', '-crf', '27', '-tune', 'animation', '-pix_fmt', 'yuv420p',
    '-g', str(FPS * 2), '-keyint_min', str(FPS * 2), '-sc_threshold', '0',
    '-c:a', 'aac', '-b:a', '32k', '-movflags', '+faststart', out], check=True)
print('done:', out)
