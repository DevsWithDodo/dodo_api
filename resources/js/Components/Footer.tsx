import React from "react";
import { Link, useLocation, useNavigate } from 'react-router-dom';

const Footer = () => {
  const currentYear = new Date().getFullYear();
  const navigate = useNavigate();
  const location = useLocation();
  
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
  };
  
  return (
    <footer className="bg-gray-100 py-12 shrink-0">
      <div className="container mx-auto px-4">
        <div className="flex flex-col md:flex-row justify-between items-center">
          <div className="flex items-center mb-6 md:mb-0">
            <img src="/assets/icon.png" alt="Dodo Logo" className="h-10 w-10 mr-2" />
            <span className="text-xl font-bold text-dodo-blue">Dodo</span>
          </div>
          
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8 md:mb-0">
            <div>
              <h3 className="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Navigation</h3>
              <ul className="space-y-3">
                <li>
                  <Link to="/" className="text-base text-gray-600 hover:text-dodo-blue">
                    Home
                  </Link>
                </li>
                <li>
                  <button 
                    onClick={() => navigateToSection('features')}
                    className="text-base text-gray-600 hover:text-dodo-blue bg-transparent border-0 p-0 cursor-pointer text-left"
                  >
                    Features
                  </button>
                </li>
                <li>
                  <button 
                    onClick={() => navigateToSection('screenshots')}
                    className="text-base text-gray-600 hover:text-dodo-blue bg-transparent border-0 p-0 cursor-pointer text-left"
                  >
                    Screenshots
                  </button>
                </li>
              </ul>
            </div>
            
            <div>
              <h3 className="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Legal</h3>
              <ul className="space-y-3">
                <li>
                  <Link 
                    to="/privacy-policy" 
                    className="text-base text-gray-600 hover:text-dodo-blue"
                  >
                    Privacy Policy
                  </Link>
                </li>
              </ul>
            </div>
            
            <div>
              <h3 className="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Resources</h3>
              <ul className="space-y-3">
                <li>
                  <a 
                    href="https://github.com/orgs/DevsWithDodo/repositories" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="text-base text-gray-600 hover:text-dodo-blue"
                  >
                    GitHub
                  </a>
                </li>
                <li>
                  <button 
                    onClick={() => navigateToSection('download')}
                    className="text-base text-gray-600 hover:text-dodo-blue bg-transparent border-0 p-0 cursor-pointer text-left"
                  >
                    Download
                  </button>
                </li>
              </ul>
            </div>
            
            <div>
              <h3 className="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Contact</h3>
              <ul className="space-y-3">
                <li>
                  <a 
                    href="mailto:support@dodoapp.net" 
                    className="text-base text-gray-600 hover:text-dodo-blue"
                  >
                    Email Us
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <div className="mt-8 pt-8 border-t border-gray-200">
          <p className="text-center text-base text-gray-500">
            &copy; {currentYear} Dodo App. All rights reserved.
          </p>
          <p className="text-center text-sm text-gray-500 mt-2">
            ❤️ Made with love by two buddies who code together.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer; 