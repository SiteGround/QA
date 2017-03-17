This code is a simple QA challenge made for the [QA: challenge accepted 3.0 conference in Sofia](http://qachallengeaccepted.com/)

The idea was to create a 20x20 RGB LED board, which will be controlled via Raspberry Pi. On the R Pi we will run Nginx and a daemon that will draw different figures on the RGB LED board.

The code is separated in three parts:
- web page which display what is on the board
- PHP API, for transporting the results to the daemon
- Python Daemon, that is responsible for the actual drawing.

We have used WS2812 RGB LEDs, Raspberry Pi 1 and [rpi_ws281x library](https://github.com/jgarff/rpi_ws281x)

The RGB LED board was fabricated and solderd by Ivan Dinkov.

The software(and the bugs introdcuded in it) was written by Marian Marinov.
