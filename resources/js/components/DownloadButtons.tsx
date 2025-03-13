import AppleLogo from '@/svg/apple-logo.svg?react';
import GooglePlayLogo from '@/svg/google-play-logo.svg?react';
import MicrosoftLogo from '@/svg/microsoft-logo.svg?react';
import React from 'react';
import { twMerge } from 'tailwind-merge';

interface DownloadButtonsProps {
  className?: string;
  showAll?: boolean;
}

const DownloadButtons = ({ className, showAll = true }: DownloadButtonsProps) => {
  return (
    <div className={twMerge("flex flex-col min-[900px]:flex-row items-center justify-center gap-6 whitespace-nowrap", className)}>
      {/* Google Play Button */}
      <a 
        href="https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla" 
        target="_blank" 
        rel="noopener noreferrer"
        className="bg-white text-gray-900 flex items-center px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
      >
        <GooglePlayLogo className="w-7 h-7 mr-3" />
        <div>
          <div className="text-xs">GET IT ON</div>
          <div className="text-xl font-semibold -mt-1">Google Play</div>
        </div>
      </a>
      
      {/* App Store Button */}
      <a 
        href="https://apps.apple.com/us/app/lender-finances-for-groups/id1558223634" 
        target="_blank" 
        rel="noopener noreferrer"
        className="bg-white text-gray-900 flex items-center px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
      >
        <AppleLogo className="w-7 h-7 mr-3" />
        <div>
          <div className="text-xs">Download on the</div>
          <div className="text-xl font-semibold -mt-1">App Store</div>
        </div>
      </a>

      {showAll && (
        <>
          {/* Microsoft Store Button */}
          <a 
            href="https://apps.microsoft.com/detail/9nvb4czjdsq7" 
            target="_blank" 
            rel="noopener noreferrer"
            className="bg-white text-gray-900 flex items-center px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
          >
            <MicrosoftLogo className="w-7 h-7 mr-3" />
            <div>
              <div className="text-xs">Download from the</div>
              <div className="text-xl font-semibold -mt-1">Microsoft Store</div>
            </div>
          </a>

          {/* Use Online */}
          <a 
            href="https://app.dodoapp.net" 
            target="_blank" 
            rel="noopener noreferrer"
            className="bg-white text-gray-900 flex items-center px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
          >
            <img src="/assets/icon.png" alt="Dodo" className="w-7 h-7 mr-3" />
            <div>
              <div className="text-xs">Use Dodo</div>
              <div className="text-xl font-semibold -mt-1">Online</div>
            </div>
          </a>
        </>
      )}
    </div>
  );
};

export default DownloadButtons; 