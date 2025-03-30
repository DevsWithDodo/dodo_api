import React, { TouchEvent, useRef, useState } from "react";
import { cn } from '../utils';

const Screenshots = () => {
  const [activeIndex, setActiveIndex] = useState(0);
  const [isHovering, setIsHovering] = useState(false);
  const touchStartXRef = useRef<number | null>(null);
  const touchStartYRef = useRef<number | null>(null);

  // Assuming we have at least 5 screenshots
  const screenshots = [
    { 
      src: '/assets/screenshots/screenshot1.png',
      alt: 'Dodo App - Main Dashboard',
      caption: 'Main Dashboard'
    },
    { 
      src: '/assets/screenshots/screenshot2.png',
      alt: 'Dodo App - Expense Tracking',
      caption: 'Expense Tracking'
    },
    { 
      src: '/assets/screenshots/screenshot3.png',
      alt: 'Dodo App - Split Bills',
      caption: 'Split Bills'
    },
    { 
      src: '/assets/screenshots/screenshot4.png',
      alt: 'Dodo App - Receipt Scanning',
      caption: 'Receipt Scanning'
    },
    { 
      src: '/assets/screenshots/screenshot5.png',
      alt: 'Dodo App - Settings',
      caption: 'Settings'
    },
    { 
      src: '/assets/screenshots/screenshot6.png',
      alt: 'Dodo App - Themes',
      caption: 'Themes'
    },
    { 
      src: '/assets/screenshots/screenshot7.png',
      alt: 'Dodo App - Currency Exchange',
      caption: 'Currency Exchange'
    },    
  ];

  const nextSlide = () => {
    setActiveIndex((current) => (current + 1) % screenshots.length);
  };

  const prevSlide = () => {
    setActiveIndex((current) => (current === 0 ? screenshots.length - 1 : current - 1));
  };

  const goToSlide = (index: number) => {
    setActiveIndex(index);
  };

  // Handle touch start event for swipe detection
  const handleTouchStart = (e: TouchEvent) => {
    touchStartXRef.current = e.touches[0].clientX;
    touchStartYRef.current = e.touches[0].clientY;
  };

  // Handle touch end event for swipe detection
  const handleTouchEnd = (e: TouchEvent) => {
    if (touchStartXRef.current === null) {
      return;
    }

    const touchEndX = e.changedTouches[0].clientX;
    const touchEndY = e.changedTouches[0].clientY;
    const xDiff = touchStartXRef.current - touchEndX;
    const yDiff = touchStartYRef.current! - touchEndY;

    // Only register a swipe if the horizontal movement is greater than vertical
    // and greater than a minimum threshold (to avoid small accidental swipes)
    if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > 50) {
      if (xDiff > 0) {
        // Swipe left, go to next slide
        nextSlide();
      } else {
        // Swipe right, go to previous slide
        prevSlide();
      }
    }

    // Reset touch start position
    touchStartXRef.current = null;
    touchStartYRef.current = null;
  };

  // Function to get screenshot indexes for the carousel display
  const getVisibleScreenshots = () => {
    const result = [];
    const totalScreenshots = screenshots.length;
    
    // Add two screenshots before the active one
    for (let i = -2; i <= 2; i++) {
      let index = (activeIndex + i + totalScreenshots) % totalScreenshots;
      result.push({
        screenshot: screenshots[index],
        index,
        position: i
      });
    }
    
    return result;
  };

  return (
    <section id="screenshots" className="py-16 md:py-24 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            See <span className="text-dodo-blue">Dodo</span> in Action
          </h2>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Take a closer look at our beautiful and intuitive interface.
          </p>
        </div>

        {/* Mobile view (single screenshot) with swipe support */}
        <div className="relative max-w-4xl mx-auto md:hidden">
          <div className="relative overflow-hidden rounded-2xl shadow-2xl aspect-[1290/2796] max-w-[280px] mx-auto"
               onMouseEnter={() => setIsHovering(true)}
               onMouseLeave={() => setIsHovering(false)}
               onTouchStart={handleTouchStart}
               onTouchEnd={handleTouchEnd}
          >
            {screenshots.map((screenshot, index) => (
              <div
                key={index}
                className={cn(
                  "absolute top-0 left-0 w-full h-full transition-opacity duration-500",
                  activeIndex === index ? "opacity-100 z-10" : "opacity-0 z-0"
                )}
              >
                <img
                  src={screenshot.src}
                  alt={screenshot.alt}
                  className="w-full h-full object-cover"
                />
              </div>
            ))}
          </div>
        </div>

        {/* Desktop view (multiple screenshots) */}
        <div 
          className="hidden md:block relative max-w-6xl mx-auto"
          onMouseEnter={() => setIsHovering(true)}
          onMouseLeave={() => setIsHovering(false)}
        >
          <div className="flex justify-center items-center relative min-h-[600px]">
            {getVisibleScreenshots().map(({ screenshot, index, position }) => (
              <div
                key={index}
                className={cn(
                  "absolute transform transition-all duration-500 rounded-2xl overflow-hidden shadow-lg",
                  {
                    // Center screenshot (active)
                    "z-30 scale-100 -translate-y-4 shadow-2xl": position === 0,
                    // First screenshots on each side
                    "z-20 scale-90 opacity-90": Math.abs(position) === 1,
                    // Second screenshots on each side
                    "z-10 scale-80 opacity-60": Math.abs(position) === 2,
                  },
                  // Position horizontally
                  position === -2 ? "-translate-x-[28rem]" : "",
                  position === -1 ? "-translate-x-[14rem]" : "",
                  position === 0 ? "translate-x-0" : "",
                  position === 1 ? "translate-x-[14rem]" : "",
                  position === 2 ? "translate-x-[28rem]" : "",
                )}
                onClick={() => goToSlide(index)}
              >
                <div className={cn(
                  "aspect-[1290/2796] cursor-pointer transition-all",
                  position === 0 ? "w-[220px]" : "w-[180px] hover:scale-105"
                )}>
                  <img
                    src={screenshot.src}
                    alt={screenshot.alt}
                    className="w-full h-full object-cover"
                  />
                </div>
              </div>
            ))}

            {/* Navigation arrows - only visible on hover */}
            <button
              onClick={prevSlide}
              className={cn(
                "absolute left-4 top-1/2 -translate-y-1/2 z-40 bg-white/80 rounded-full p-3 shadow-md hover:bg-white transition-all duration-200",
                isHovering ? "opacity-100" : "opacity-0"
              )}
              aria-label="Previous screenshot"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" className="w-6 h-6 text-gray-700">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              onClick={nextSlide}
              className={cn(
                "absolute right-4 top-1/2 -translate-y-1/2 z-40 bg-white/80 rounded-full p-3 shadow-md hover:bg-white transition-all duration-200",
                isHovering ? "opacity-100" : "opacity-0"
              )}
              aria-label="Next screenshot"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" className="w-6 h-6 text-gray-700">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        {/* Dots for both views */}
        <div className="flex justify-center mt-8 space-x-2">
          {screenshots.map((_, index) => (
            <button
              key={index}
              onClick={() => goToSlide(index)}
              className={cn(
                "w-3 h-3 rounded-full transition-colors duration-200",
                activeIndex === index ? "bg-dodo-blue" : "bg-gray-300 hover:bg-gray-400"
              )}
              aria-label={`Go to screenshot ${index + 1}`}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

export default Screenshots; 