import React, { useState } from "react";
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { cn } from '../lib/utils';

const Navbar = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  // Smart navigation that works from any page
  const navigateToSection = (sectionId: string) => {
    if (location.pathname !== '/') {
      // If not on home page, navigate to home first
      navigate('/');
      // Wait for navigation to complete before scrolling
      setTimeout(() => {
        const element = document.getElementById(sectionId);
        if (element) {
          element.scrollIntoView({ behavior: 'smooth' });
        }
      }, 100);
    } else {
      // Already on home page, just scroll
      const element = document.getElementById(sectionId);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
      }
    }
    // Close mobile menu if open
    if (isMenuOpen) {
      setIsMenuOpen(false);
    }
  };

  return (
    <nav className="bg-white shadow-sm shrink-0">
      <div className="container mx-auto px-4 py-3">
        <div className="flex justify-between items-center">
          <div className="flex items-center space-x-2">
            <img src="/assets/icon.png" alt="Dodo Logo" className="h-10 w-10" />
            <span className="text-2xl font-bold text-dodo-blue">Dodo</span>
          </div>

          {/* Desktop menu */}
          <div className="hidden md:flex items-center space-x-8">
            <Link to="/" className="text-gray-700 hover:text-dodo-blue transition-colors">
              Home
            </Link>
            <button 
              onClick={() => navigateToSection('features')}
              className="text-gray-700 hover:text-dodo-blue transition-colors bg-transparent border-0 p-0 cursor-pointer"
            >
              Features
            </button>
            <button 
              onClick={() => navigateToSection('screenshots')}
              className="text-gray-700 hover:text-dodo-blue transition-colors bg-transparent border-0 p-0 cursor-pointer"
            >
              Screenshots
            </button>
            <a 
              href="https://github.com/orgs/DevsWithDodo/repositories" 
              target="_blank" 
              rel="noopener noreferrer"
              className="text-gray-700 hover:text-dodo-blue transition-colors"
            >
              GitHub
            </a>
            <Link 
              to="/privacy-policy" 
              className="text-gray-700 hover:text-dodo-blue transition-colors"
            >
              Privacy
            </Link>
            <button 
              onClick={() => navigateToSection('download')}
              className="btn btn-primary"
            >
              Download App
            </button>
          </div>

          {/* Mobile menu button */}
          <div className="md:hidden">
            <button 
              onClick={toggleMenu}
              className="text-gray-700 hover:text-dodo-blue focus:outline-none"
              aria-expanded={isMenuOpen}
              aria-label="Toggle menu"
            >
              <svg 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor" 
                className="h-6 w-6"
              >
                {isMenuOpen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>
          </div>
        </div>

        {/* Mobile menu */}
        <div className={cn(
          "md:hidden mt-2 pb-3 space-y-1",
          isMenuOpen ? "block" : "hidden"
        )}>
          <Link 
            to="/" 
            className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-dodo-blue hover:bg-gray-50"
            onClick={() => setIsMenuOpen(false)}
          >
            Home
          </Link>
          <button 
            onClick={() => navigateToSection('features')}
            className="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-dodo-blue hover:bg-gray-50 bg-transparent border-0"
          >
            Features
          </button>
          <button 
            onClick={() => navigateToSection('screenshots')}
            className="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-dodo-blue hover:bg-gray-50 bg-transparent border-0"
          >
            Screenshots
          </button>
          <a 
            href="https://github.com/orgs/DevsWithDodo/repositories" 
            target="_blank" 
            rel="noopener noreferrer"
            className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-dodo-blue hover:bg-gray-50"
            onClick={() => setIsMenuOpen(false)}
          >
            GitHub
          </a>
          <Link 
            to="/privacy-policy" 
            className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-dodo-blue hover:bg-gray-50"
            onClick={() => setIsMenuOpen(false)}
          >
            Privacy
          </Link>
          <button 
            onClick={() => navigateToSection('download')}
            className="block w-full text-left px-3 py-2 rounded-md text-base font-medium bg-dodo-blue text-white"
          >
            Download App
          </button>
        </div>
      </div>
    </nav>
  );
};

export default Navbar; 